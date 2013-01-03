<?php
/*
 * TWCMS 1.x
 * Global Include File
 *
 * Loads global config, core libraries, and
 * loads all modules and mod configs
 */

// Always report all errors
error_reporting(E_ALL ^ E_DEPRECATED);

// Security global used by includes
define('SECURITY', TRUE);

// For calculating page load times
$_start = microtime(TRUE);

// Unset deprecated variables that polute error reports
// and are generally just a nuisance (Only affects older PHP Versions)
unset($HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_COOKIE_VARS,
	$HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_POST_FILES,
	$HTTP_SESSION_VARS);

/*
 * <TWCMS> Core
 * Important constants frequently used by TWCMS
 * Do NOT Edit
 */

// CMS Version, update on each new release
define('VERSION', 'TWCMS 1.5');

// Utility constants
define('NOW', time());
define('SSL', isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on');

// Check if the request came from AJAX
// Automatically set by most JS Libraries
define('AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// Capture important server headers:
// Full requested PATH, IP Address and Referer
define('REQUESTURL', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');

define('USERIP', isset($_SERVER['REMOTE_ADDR']) ?
	htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, 'UTF-8') : 'N/A');

define('REFERER', isset($_SERVER['HTTP_REFERER']) ?
	htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '(Direct)');

/***
 * End Constants
 ***/

// Load base libraries
require dirname(__FILE__).'/config.inc.php';
require LPATH.'twcore.inc.php';
require LPATH.'security.inc.php';
require LPATH.'utility.inc.php';
require LPATH.'processing.inc.php';
require LPATH.'template.inc.php';

// Start session tracking
ini_set('session.use_only_cookies', TRUE);
session_name(PREFIX);
session_start();

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

tw_event('onLoad');

// EOF
