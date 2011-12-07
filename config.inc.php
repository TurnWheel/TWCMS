<?php
/*
 * TWCMS 0.9-Beta
 * Global Configuration File
 *
 * Coded by Steven Bower
 * TurnWheel Designs - http://turnwheel.com/
 */

$cfg = array(
	/* Script Config */
	// Set to TRUE to enable "Debug" mode (Not recommended for live sites)
	'debug' => TRUE,
	'email_date' => 'g:ia T \o\n F j, Y', // Email date format

	/****
	 * Processing Config
	 * Resource settings apply to both CSS and JS files
	 ****/

	/*
	 * Checks root name for resources
	 * If FALSE, it will not load parent resources for subpages
	 * Ex: on page /about/history, it will not check for about.css
	 * Default is TRUE, so both about.js & about_history.js will be loaded
	 */
	'res_checkRoot' => TRUE,

	/*
	 * Resource Recursive Check
	 * Default: FALSE
	 * If TRUE, in case of /about/some/person;
	 * about.js, about_some.js, and about_some_person.js would loaded
	 * If FALSE, just about.js and about_some_person.js
	 * unless res_checkRoot is FALSE
	 *
	 * Recommended to keep off unless really needed,
	 * as it can be inefficient for large menu tress.
	 */
	'res_recursive' => FALSE,

	/* Database Settings */
	'db_enable' => TRUE, // Is DB connection even required?
	// Host name for SQL Database ('localhost' works for most installations)
	'db_host' => 'localhost',
	// Username for SQL login (Recommend you do NOT use root)
	'db_user' => 'somewebsite',
	'db_pass' => 'SQLPass123', // Password for SQL user
	'db_name' => 'somewebsite', // MySQL Database name

	/* Encryption Settings */
	// Algorithm for generating one-way hashes
	// (Default: sha512); see php.net/hash_algos for options
	'hash_algo' => 'sha512',

	// Algorithm for generating two-way enc keys
	// (Default: MCRYPT_3DES); see mcrypt.ciphers for options
	'enc_algo' => 'tripledes',

	// Seed key for enc_algo (NOTE: CHANGE ONLY DURING INITIAL INSTALL)
	'enc_key' => 'CHANGE ME ONCE',

	/* Contact Page Settings */
	// List of emails to send contact requests to
	'contact_admin' => array('yourname@example.com'),
	// Headers used in emails
	'contact_headers' => 'From: SomeWesbite<contact@somewebsite.com>',
	'contact_content' => array('subject' => 'Contacted by {name}',
'body' => '{name} has contacted you through SomeWebsite.com:

{message}

------
Replies to this email go to {email}
Message Sent @ {date}
Automated Email Sent By SomeWebsite.com'
	),

	// Admin Login
	'admin' => array('user' => 'admin','pass' => 'somedude'),

	// Image Upload Settings (if needed)
	'image_types' => array('png','jpg','jpeg'),
	'image_size' => 600, // Size determines length of largest side
	'image_size_th' => 150, // Size of thumbnail

	// Mail Chimp Settings
	'mc_enable' => FALSE, // Enable API

	// Mail Chimp End Points (See Docs)
	'mc_ep' => 'http://us2.api.mailchimp.com/1.3/',

	'mc_key' => '{paste key}', // API Key (login to account to edit/add)
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
define('VERSION', 'TWCMS 0.9-Beta'); // CMS Version

// Save User's IP Address as constant
define('USERIP', isset($_SERVER['REMOTE_ADDR']) ?
	htmlspecialchars($_SERVER['REMOTE_ADDR']) : 'N/A');

// EOF
