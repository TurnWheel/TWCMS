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
$_starttime = microtime(TRUE);

// Start session tracking
session_start();

// Load base libraries
require dirname(__FILE__).'/config.inc.php';
require LPATH.'utility.inc.php';
require LPATH.'security.inc.php';
require LPATH.'process.inc.php';

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
