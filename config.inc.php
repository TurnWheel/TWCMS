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
	'debug' => TRUE, // Set to TRUE to enable "Debug" mode (Not recommended for live sites)

	/* Database */
	'db_enable' => TRUE, // Is DB connection even required? (Base CMS doesn't use it, but most addons will!)
	'db_host' => 'localhost', // Host name for MySQL Database ('localhost' works for most installations)
	'db_user' => 'somewebsite', // Username for MySQL login (Recommend you do NOT use root)
	'db_pass' => 'SQLPass123', // Password for MySQL user
	'db_name' => 'somewebsite', // MySQL Database name

	// Email Settings
	'email_date' => 'g:ia T \o\n F j, Y', // Email date format

	// Encryption Settings
	'hash_algo' => 'sha512', // Algorithm for generating one-way hashes (Default: sha512); see php.net/hash_algos for options
	'enc_algo' => 'tripledes', // Algorithm for generating two-way enc keys (Default: MCRYPT_3DES); see mcrypt.ciphers for options
	'enc_key' => 'CHANGE ME ONCE', // Seed key for enc_algo (NOTE: CHANGE ONLY DURING INITIAL INSTALL)

	/* Contact Page Settings */
	'contact_admin' => array('steven@turnwheel.com'), // List of emails to send contact requests to
	//'contact_admin' => array('jax@turnwheel.com'),
	'contact_headers' => 'From: SomeWesbite<contact@somewebsite.com>', // Headers used in emails
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
	'mc_ep' => 'http://us2.api.mailchimp.com/1.3/', // Mail Chimp End Points (See Docs)
	'mc_key' => '{paste key}', // API Key (login to account to edit/add)
	'mc_listid' => 'effa235ac8', // Unique list id for list to be used
);

/* Define constants */
define('LPATH', '/www/SomeWebsite.com/www/lib/'); // Abs. local path to library files

// PREFIX Note: If changed, you must rename all your CSS and JS files!
define('PREFIX', 'pre'); // Prefix used for cookies, file names, etc.

define('IMGPATH', '/www/SomeWebsite.com/www/uploads/'); // Image upload directory (full path)
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
define('FULLURL', SSL ? SSLURL : WWWURL); // Full URL is the current full domain path
define('REQUESTURL', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'); // Get requested path
define('VERSION', 'TWCMS 0.9-Beta'); // CMS Version

// Save User's IP Address as constant
define('USERIP', isset($_SERVER['REMOTE_ADDR']) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : 'N/A');

// EOF
