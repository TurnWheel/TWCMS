<?php
/*
 * Coded by Steven Bower
 * TurnWheel Designs (cc) 2011
 *
 * Provides simple hook for JSON based
 * mailchimp API. This is designed to be
 * super simple, and provides only ADDING
 * of people to API. For more advanced functions,
 * please seek the mailchimp API docs.
 */

if (!defined('SECURITY')) exit;

/*
 * $data is an array of input
 * accepts any MVAR, and
 * REQUIRES 'email' key
 * for sending email_address
 */
function mc_send($data) {
	global $cfg;

	// Mail chimp must be enabled
	// AND email must be set in $data
	if (!isset($cfg['mc_enable']) || !$cfg['mc_enable']
			|| empty($data) || !isset($data['email'])) {
		return FALSE;
	}

	// Save and unset email addr
	$email = $data['email'];
	unset($data['email']);

	// API merge_vars
	// See documentation for options
	$mvars = array('FNAME' => $data['fname'], 'LNAME' => $data['lname']);

	// Setup main data to send
	$url = $cfg['mc_ep'].'?method=listSubscribe';
	$mcdata = array(
		'apikey' => $cfg['mc_key'],
		'id' => $cfg['mc_listid'],
		'email_address' => $email,
		'email_type' => 'html',
		'merge_vars' => $mvars,
		'double_optin' => TRUE,
		'send_welcome' => FALSE,
		'update_existing' => FALSE,
	);

	$payload = json_encode($mcdata);

	// Load up CURL and send payload
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));

	$result = curl_exec($ch);
	curl_close($ch);

	// Handle Response
	$resp = json_decode($result);
	if (isset($resp->error) && $resp->error) {
		$mc_status = $resp->code .' : '.$resp->error;
	}
	else $mc_status = TRUE;

	return $mc_status;
}

// EOF
