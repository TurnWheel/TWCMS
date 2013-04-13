<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 0.5
 * Author: Steven Bower
 * TurnWheel Designs (cc) 2012
 *
 * Useful form integrated form system
 * Includes sending emails and saving to DB
 *
 * ---- Additional Files ----
 * content/contact.inc.php
 * content/admin_forms.inc.php
 * ----
 */

if (!defined('SECURITY')) exit;

/* Displays menu link in admin */
function forms_adminMenu() {
	$menu = array(
		'url' => '/admin/forms/',
		'text' => 'View Form Data',
		'descrip' => 'Displays all saved data from various forms,
		such as contact form'
	);

	if (tw_isloaded('user')) {
		$menu['perms'] = FORMS_U_ADMIN;
	}

	return $menu;
}

/*
 * Process form data
 *
 * $errors -> Returns array of errors by ref
 */
function form_process($name, &$error) {
	global $cfg;

	// Make sure we have settings for this form
	if (!isset($cfg['forms'][$name])) return FALSE;

	// Form variables
	$fcfg = $cfg['forms'][$name];
	$data = array();
	$fields = $fcfg['fields'];
	$required = $fcfg['required'];

	// Get specified form fields
	foreach ($fields AS $field) {
		$data[$field] = isset($_POST[$field]) ?
			html_escape($_POST[$field]) : '';
	}

	// Check if form was even submitted at this point
	// If so, just return with existing data
	if (!isset($_POST['submit'])) return $data;

	// Verify required fields
	foreach ($required AS $field) {
		if (empty($data[$field]) || $data[$field] === 0) {
			$error[$field] = TRUE;
		}
	}

	// Verify email with special case
	if (isset($data['email']) && array_search('email', $required) !== FALSE
			&& !isset($error['email'])
			&& !valid_email($data['email'])) {
		$error['email'] = TRUE;
	}

	// If there are errors at this point, end processing
	if (sizeof($error) !== 0) return $data;

	// Save data to DB if enabled
	if (tw_isloaded('sql') && $fcfg['savedb']) {
		sql_query('INSERT INTO forms SET data = "%s", date = "%d", name = "%s"',
			array(serialize($data), NOW, $name), __FILE__, __LINE__);
	}

	// Call newsletter event if newsletter option is enabled
	// and newsletter field is set in POST data
	if (isset($fcfg['newsletter']) && $fcfg['newsletter']
		&& isset($fcfg['newsletter_field'])
		&& isset($_POST[$fcfg['newsletter_field']])) {
		$subinput = array('email' => $data['email']);
		tw_event('onSubscribe', FALSE, $subinput);
	}

	/* Handle emailing */

	// Generate map of form variables
	$map = array();
	foreach ($data AS $k => $v) {
		$map[$k] = html_escape(str_replace('\r\n', "\n", $v));
	}

	// First replace to email with form data
	// if specified field exists
	$ucfg = $fcfg['emails']['user'];
	if (isset($ucfg['to']) && isset($data[$ucfg['to']])) {
		$ucfg['to'] = $data[$ucfg['to']];

		tw_sendmail($ucfg, $map);
	}

	// Send Out Admin Email
	tw_sendmail($fcfg['emails']['admin'], $map);

	// Check for redirection
	if (is_string($fcfg['redirect'])) {
		header('Location: '.$fcfg['redirect']);
		exit;
	}

	return $data;
}

// EOF
