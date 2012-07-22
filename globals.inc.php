<?php
/*
 * TWCMS 1.1
 * Global Include File
 *
 * Loads global config, core libraries, and
 * loads all modules and mod configs
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
require LPATH.'twcore.inc.php';
require LPATH.'security.inc.php';
require LPATH.'utility.inc.php';
require LPATH.'processing.inc.php';
require LPATH.'template.inc.php';

// Move $_start to $cfg
$cfg['start_time'] = $_start;
unset($_start);

// Encrypt all auth passwords
foreach ($cfg['auth'] AS $name => $login) {
	$cfg['auth'][$name]['pass'] = tw_genhash($login['pass']);
}

// Re-sets "X-Powered-By" header with CMS Version
// This helps override some servers' PHP Disclosure settings
header('X-Powered-By: '.VERSION);

// Template Variable
// All values the template will need should be in the $T array.
$T = array();

/*
 * Load all modules, configs, and call module onLoad events
 */
foreach ($cfg['mods_enabled'] AS $mod) {
	tw_loadmod($mod);
}

// EOF
