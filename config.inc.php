<?php
/*
 * TWCMS 1.0
 * Global Configuration File
 *
 * Coded by Steven Bower
 * TurnWheel Designs (cc) 2012
 */

$cfg = array(
	/* Script Config */
	// Set to TRUE to enable "Debug" mode (Not recommended for live sites)
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
	 * as it can be inefficient for large menu tress.
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
		'sql', 'forms', 'user', 'mailchimp'
	),

	// Used internally to track which modules have been loaded
	'mods_loaded' => array(),

	/* Database Settings */
	'sql_enable' => TRUE, // Is DB connection even required?
	// Host name for SQL Database ('localhost' works for most installations)
	'sql_host' => 'localhost',
	// Username for SQL login (Recommend you do NOT use root)
	'sql_user' => 'somewebsite',
	'sql_pass' => 'SQLPass123', // Password for SQL user
	'sql_name' => 'somewebsite', // MySQL Database name

	/* Form Settings */
	// Array of form settings (Key is its name)
	'forms' => array(
		'contact' => array(
			// Array of field names to process
			'fields' => array('name', 'email', 'message'),

			// Array of required fields
			'required' => array('name', 'email', 'message'),

			// Save received data to DB?
			// Requires 'sql' module to be enabled
			'savedb' => FALSE,

			// Redirect to page after submission?
			// If FALSE, not redirect happens.
			// If a string, it uses string as destination URL
			'redirect' => '/contact/thankyou',

			// ** Email Configurations **

			// Format of dates in emails
			'email_date' => 'g:ia T \o\n F j, Y',

			// "admin" and "user" are static options
			'emails' => array(
				'admin' => array(
					'enable' => TRUE,

					// Array of emails to send to
					// For admin only. For user is must be a fieldname
					'to' => array('yourname@example.com'),

					// Subject of email
					'subject' => 'Contacted by {name}',

					// Additional email headers (optional)
					// From: and Reply-To: are most common
					// You can enter in {field} to use data here
					// Example: From: {name}<{email}>
					'headers' => 'From: {name}<{email}>',

					// Body of email with {} for var replacements
					// {date} is provided by the library
					// Tabs (\t) are automatically stripped
					'body' => '{name} has contacted you through SomeWebsite.com:

					{message}

					------
					Replies to this email go to {email}
					Message Sent @ {date}
					Automated Email Sent By SomeWebsite.com'
				),
				'user' => array(
					'enable' => FALSE,
					// Field name to grab "to" email from
					'to' => 'email',
					'subject' => 'Contacted by {name}',
					'headers' => 'From: SomeWebsite<contact@somewebsite.com>',
					'body' => ''
				),
			),
		),
	),

	/* User Settings */
	// Enable user login and registration systems
	// requires that sql_enable = TRUE and user table has been created
	'user_enable' => TRUE,
	// Seconds until cookies expire (default 604800, or 1 month)
	'user_expire' => 604800,

	/* MailChimp Settings */
	'mailchimp_enable' => FALSE, // Enable API

	// Mail Chimp End Points (See Mailchimp Docs For Details)
	'mc_ep' => 'http://us2.api.mailchimp.com/1.3/',
	'mc_key' => '{paste key}', // API Key (account login)
	'mc_listid' => 'effa235ac8', // Unique list id for list to be used
);

/* Define constants */
// Abs. local path to library files
define('LPATH', '/www/SomeWebsite.com/www/lib/');

// PREFIX Note: If changed, you must rename all your CSS and JS files!
// css/<prefix>.<page>.css
// js/<prefix>.<page>.js
define('PREFIX', 'pre'); // Prefix used for cookies, file names, etc.

// Image upload directory (full path)
define('IMGPATH', '/www/SomeWebsite.com/www/uploads/');
define('IMGURL', '/uploads/'); // Image upload URL (relative path)
define('CPATH', 'content/'); // Path to content directory
define('DOMAIN', 'SomeWebsite.com'); // Root domain name of website
define('BASEURL', '/'); // Relative path from root
define('SSLURL', 'https://'.DOMAIN.'/'); // Path to SSL server
define('WWWURL', 'http://'.DOMAIN.'/'); // Path to main website

// Maps settings
define('MAPS_HOST', 'maps.google.com');
// SomeWebsite.com
define('MAPS_KEY', 'ABQIAAAAZReS-Ex4akb7OZJr5kruGxQCvPwXk464zndFkQpy_L80v-esWBSEOIUIQxIGb9olf2owYHTsqjjZGg');
// dev.somewebsite.com
//define('MAPS_KEY', 'ABQIAAAAZReS-Ex4akb7OZJr5kruGxTOYl9STBTHMd_HDDjgXxc08qZ7wBRvNiTUXiXoGUcFgP0pf4mTJPAfcw');

/*
 * Define Bit Flags
 * Do not edit unless you know
 * what you are doing!
 */

/* User Flags (Permissions) */
if ($cfg['user_enable']) {
	define('U_LOGIN', 1); // Basic login privledges
	define('U_EDITOR', 2); // (Optional)
	define('U_ADMIN', 4);

	// Default perms for new accounts
	define('U_DEFAULT', 1);

	// Default perms for guests
	define('U_GUEST', 0);
}

/* Other Flags [CUSTOM] */
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
define('REQUESTURL',isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
define('VERSION', 'TWCMS 1.0-U'); // CMS Version

// Save User's IP Address as constant
define('USERIP', isset($_SERVER['REMOTE_ADDR']) ?
	htmlspecialchars($_SERVER['REMOTE_ADDR']) : 'N/A');

// EOF
