<?php
/*
 * TWCMS 1.x
 * Global Configuration File
 *
 * All the core settings for TWCMS are located here
 * Modules add to the global array through mods/<mod>/<mod>.cfg.php
 *
 * Important constants used throughout the API are set here
 */

if (!defined('SECURITY')) exit;

$cfg = array(
	// Set to TRUE to enable "Debug" mode
	// This should be disabled for live sites
	'debug' => TRUE,

	/****
	 * Processing Config
	 * Resource settings apply to both CSS and JS files
	 ****/

	/*
	 * If set to TRUE, parents will be checked if page is not found.
	 *
	 * Example: /about/some, if about_some is not found
	 * "about" will be checked before throwing 404 error
	 *
	 * Default FALSE, so parent will not be checked
	 */
	'p_tryParents' => FALSE,

	/*
	 * Show parent pages in title like breadcrumbs
	 */
	'p_crumbTitles' => TRUE,

	/*
	 * Load resources for root pages on subpages?
	 *
	 * If FALSE, it will not load parent resources for subpages
	 * Ex: on page /about/history, it will not check for about.js/css
	 *
	 * Default is TRUE, so both about.js & about_history.js will be loaded
	 */
	'res_checkRoot' => TRUE,

	/*
	 * Resource Recursive Check
	 * Default: FALSE
	 *
	 * If TRUE, in case of /about/some/person;
	 * about.js, about_some.js, and about_some_person.js would loaded
	 *
	 * If FALSE, just about.js and about_some_person.js
	 * unless res_checkRoot is FALSE, then just about_some_person.js
	 *
	 * Recommended to keep off unless really needed,
	 * as it can be inefficient for large menu trees.
	 */
	'res_recursive' => FALSE,

	/* Encryption Settings */
	// Algorithm for generating one-way hashes
	// (Default: sha512); see php.net/hash_algos for options
	'hash_algo' => 'sha512',

	// Algorithm for generating two-way enc keys
	// (Default: MCRYPT_3DES); see mcrypt.ciphers for options
	'enc_algo' => 'tripledes',

	// Seed key for enc_algo (NOTE: CHANGE ONLY DURING INITIAL INSTALL)
	'enc_key' => 'CHANGE ME ONCE',

	/*
	 * HTTP Auth Logins
	 * By default just 'admin' for admin areas
	 * This becomes meaningless if user mod is enabled,
	 * but both can be used for different areas if desired
	 *
	 * Passwords are automatically encrypted using hash_algo
	 * on each page load (globals.inc.php)
	 */
	'auth' => array(
		'admin' => array('user' => 'admin','pass' => 'somedude'),
	),

	// Image Upload Settings (if needed)
	'image_types' => array('png','jpg','jpeg'),
	'image_size' => 600, // Size determines length of largest side
	'image_size_th' => 150, // Size of thumbnail

	/* Email settings */
	// Default date format used in email.
	// Can be change on a per-email basis
	'email_date' => 'g:ia T \o\n F j, Y',

	// Track all emails sent in DB?
	// Requires sql module and schema/email.sql schema
	'email_savedb' => FALSE,

	/*
	 * Module Settings
	 *
	 * mods_enabled is an array of mods to be loaded
	 * in global include
	 *
	 * Add the mod to MPATH dir, with option cfg file
	 * and then add to this list. Mods are not auto-loaded
	 * from the MPATH dir
	 *
	 * <mod>_onLoad function is called during library init
	 */
	'mods_enabled' => array(
		'sql', 'error', 'forms',
		'user', 'mailchimp'
	),

	// Used internally to track which modules have been loaded
	'mods_loaded' => array()
);

/* Define constants */
// Abs. local path to root directory
define('RPATH', '/www/SomeWebsite.com/www/');

/*
 * PREFIX Note: If changed, you must rename all your CSS and JS files!
 * recommended to change on initial creation
 *
 * Files:
 * css/<prefix>.<page>.css
 * js/<prefix>.<page>.js
 *
 * Also used for cookie names.
 */
define('PREFIX', 'cms');

// Root domain name for this website
define('DOMAIN', 'SomeWebsite.com');
// Relative URL from root ('/' unless in subdir)
define('BASEURL', '/');

// Additional Directories
define('IMGPATH', RPATH.'uploads/'); // Image upload directory (full path)
define('IMGURL', BASEURL.'uploads/'); // Image upload URL (relative path)
define('LPATH', RPATH.'lib/'); // Path to main lib files
define('MPATH', RPATH.'mods/'); // Path to module files
define('CPATH', RPATH.'content/'); // Path to content files
define('SSLURL', 'https://'.DOMAIN.'/'); // Path to SSL server
define('WWWURL', 'http://'.DOMAIN.'/'); // Path to main website

/*
 * Do Not Edit Below This Line
 * These values should never change
 */

// CMS Version, update on each new release
define('VERSION', 'TWCMS 1.5-RC1');

// Utility constants
define('NOW', time());
define('SSL', isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on');

// Full URL is the current full domain path
// (Basically same as WWWURL, but accounts for SSL)
define('FULLURL', SSL ? SSLURL : WWWURL);

// Check if the request came from AJAX
define('AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// Capture important server headers:
// Full requested PATH, IP Address and Referer
define('REQUESTURL', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');

define('USERIP', isset($_SERVER['REMOTE_ADDR']) ?
	htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, 'UTF-8') : 'N/A');

define('REFERER', isset($_SERVER['HTTP_REFERER']) ?
	htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '(Direct)');

// EOF
