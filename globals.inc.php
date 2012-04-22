<?php
/*
 * TWCMS 1.0
 * Global Include File
 *
 * Coded by Steven Bower
 * TurnWheel Designs (cc) 2012
 */

// Config Changes
error_reporting(E_ALL ^ E_DEPRECATED);

// Security Global
define('SECURITY', TRUE);

// For calculating page load times
$_start = microtime(TRUE);

// Unset deprecated variables that polute error reports
// and are generally just a nuisance
unset($HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_COOKIE_VARS,
		$HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_POST_FILES,
		$HTTP_SESSION_VARS);

// Start session tracking
session_start();

// Load base libraries
require dirname(__FILE__).'/config.inc.php';
require LPATH.'utility.inc.php';
require LPATH.'security.inc.php';
require LPATH.'process.inc.php';
require LPATH.'template.inc.php';

// Move $_start to $cfg
$cfg['start_time'] = $_start;
unset($_start);

// Encrypt admin pass so it's not stored as plain text
if (isset($cfg['admin']['pass'])) {
	$cfg['admin']['pass'] = tw_genhash($cfg['admin']['pass']);
}

// Load available modules
foreach ($cfg['mods_avail'] AS $mod) {
	tw_loadmod($mod);
}

// Capture Referer Information
$cfg['referer'] = isset($_SERVER['HTTP_REFERER']) ?
					escape($_SERVER['HTTP_REFERER']) : '(Direct)';

// Re-sets "X-Powered-By" header with CMS Version
// This helps override some servers' PHP Disclosure settings
header('X-Powered-By: '.VERSION);

// EOF
