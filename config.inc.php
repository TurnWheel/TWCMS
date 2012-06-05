<?php
/*
 * TWCMS 1.0
 * Global Configuration File
 *
 * Coded by Steven Bower
 * TurnWheel Designs (cc) 2012
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

	// Admin Login
	'admin' => array('user' => 'admin','pass' => 'somedude'),

	// Image Upload Settings (if needed)
	'image_types' => array('png','jpg','jpeg'),
	'image_size' => 600, // Size determines length of largest side
	'image_size_th' => 150, // Size of thumbnail

	// Email settings
	// Default date format used in email.
	// Can be change on a per-email basis
	'email_date' => 'g:ia T \o\n F j, Y',

	// Track all emails sent in DB?
	// Requires sql module and email.sql schema
	'email_savetoDB' => FALSE,

	/*
	 * Module Settings
	 *
	 * mods_avail just passes the names onto
	 * tw_loadmod in lib/security.inc.php
	 * They can be disabled with <mod>_enable => FALSE flag
	 *
	 * For custom mods, just add it to the list with
	 * the proper naming convention.
	 *
	 * <mod>_onload function is called during library init
	 */
	'mods_avail' => array(
		'sql', 'error', 'forms',
		'user', 'mailchimp'
	),

	// Used internally to track which modules have been loaded
	'mods_loaded' => array(),
);

/* Define constants */
// Abs. local path to root directory
define('RPATH', '/www/SomeWebsite.com/www/');

// PREFIX Note: If changed, you must rename all your CSS and JS files!
// css/<prefix>.<page>.css
// js/<prefix>.<page>.js
define('PREFIX', 'pre'); // Prefix used for cookies, file names, etc.

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
 * Define Bit Flags
 * Do not edit unless you know what you are doing!
 */

define('T_APPROVE', 1); // JUST A SAMPLE

/*
 * Do Not Edit Below This Line
 * These values should never change
 */

define('NOW', time());
define('SSL', isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on');

// Full URL is the current full domain path
// Just takes into account SSL
define('FULLURL', SSL ? SSLURL : WWWURL);

// Get requested path
define('REQUESTURL', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
define('VERSION', 'TWCMS 1.0-M'); // CMS Version

// Save User's IP Address as constant
define('USERIP', isset($_SERVER['REMOTE_ADDR']) ?
		htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, 'UTF-8') : 'N/A');

// EOF
