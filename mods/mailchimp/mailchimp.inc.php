<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 0.1
 * Author: Steven Bower
 * TurnWheel Designs (cc) 2012
 *
 * Provides simple hook for JSON based mailchimp API.
 * This is designed to be super simple, and provides only
 * ADDING of people to API. For more advanced functions,
 * please seek the mailchimp API docs.
 */

if (!defined('SECURITY')) exit;

function mailchimp_onSubscribe($data) {
	return mailchimp_send($data);
}

/*
 * $data is an array of input accepts any MVAR, and
 * REQUIRES 'email' key for entered user
 */
function mailchimp_send($data) {
	global $cfg;

	// email must be set in $data
	if (empty($data) || !isset($data['email'])) {
		return FALSE;
	}

	// API merge_vars
	// See documentation for options
	$mvars = array(
		'FNAME' => isset($data['fname']) ? $data['fname'] : '',
		'LNAME' => isset($data['lname']) ? $data['lname'] : ''
	);

	// Setup main data to send
	$url = $cfg['mailchimp']['ep'].'?method=listSubscribe';
	$mcdata = array(
		'apikey' => $cfg['mailchimp']['key'],
		'id' => $cfg['mailchimp']['listid'],
		'email_address' => $data['email'],
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
