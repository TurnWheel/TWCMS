<?php
/*
 * TWCMS <Module>
 *
 * Error Module Config
 */

$cfg = array(
	// Display custom php error template?
	// Will load error.php.html. If debug mode is on, it will load
	// error.php.debug.html, which will show full error details.
	// Debug should be disabled on live production sites
	'error_template' => TRUE,

	// Save error details to DB for record keeping?
	// Requires schema/error.sql to be loaded manually
	// Also allows access to content/admin_error.inc.php
	'error_savedb' => TRUE,

	// Error email configurations
	'error_email' => array(
		'enable' => TRUE, // Enable this email?
		'date' => 'g:ia T \o\n F j, Y', // Date format for emails
		'to' => array('errors@turnwheel.com'),
		'subject' => '[Error Report]',
		'headers' => 'From: errors@turnwheel.com',
		'body' => 'Error occurred at {date}
-Error Details-
Error: {error_str}
Errno: {error_num} ({error_name})
File: {error_file}
Line: {error_line}

Full report can be viewed at: {url}admin/error/{error_sqlid}

This email was auto-generated by {version}',
	),

	/* Static config values */

	// Array of error values and their string equivolent
	'error_vals' => array(
		'1' => 'E_ERROR',
		'2' => 'E_WARNING',
		'4' => 'E_PARSE',
		'8' => 'E_NOTICE',
		'16' => 'E_CORE_ERROR',
		'32' => 'E_CORE_WARNING',
		'64' => 'E_COMPILE_ERROR',
		'128' => 'E_COMPILE_WARNING',
		'256' => 'E_USER_ERROR',
		'512' => 'E_USER_WARNING',
		'1024' => 'E_USER_NOTICE',
		'2047' => 'E_ALL',
		'2048' => 'E_STRICT'
	)
);

// EOF
