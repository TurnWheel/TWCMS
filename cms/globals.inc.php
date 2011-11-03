<?php
/*
 * TWCMS 0.9-Beta
 * Global Include File
 *
 * Coded by Steven Bower
 * TurnWheel Designs (cc) 2011
 */

// Config Changes
error_reporting(E_ALL^E_DEPRECATED);

// Security Global
define('SECURITY', TRUE);

// For calculating page load times
$_starttime = microtime(TRUE);

// Start session tracking
session_start();

// Files
require dirname(__FILE__).'/config.inc.php';
require LPATH.'utility.inc.php';
require LPATH.'security.inc.php';

// Load SQL if db is enabled
if ($cfg['db_enable']) {
	require LPATH.'sql.inc.php';

	sql_connect($cfg['db_host'],$cfg['db_user'],$cfg['db_pass'],$cfg['db_name']); // Connect to SQL Server
	unset($cfg['db_user'],$cfg['db_pass']); // Security Measure
}

// Encrypt admin pass so it's not stored in plain text
if (isset($cfg['admin']['pass'])) {
	$cfg['admin']['pass'] = tw_genhash($cfg['admin']['pass']);
}

// Capture Referer Information
$cfg['referer'] = isset($_SERVER['HTTP_REFERER']) ? real_escape($_SERVER['HTTP_REFERER']) : '(Direct)';

// Re-sets "X-Powered-By" header with CMS Version
// This helps override some servers' PHP Disclosure settings
header('X-Powered-By: '.VERSION);

// EOF
