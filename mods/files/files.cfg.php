<?php
/*
 * TWCMS <Module>
 *
 * Files Module Config
 */

$cfg = array(
	// Accepted image types
	'types' => array(
		// Archives
		'zip', 'rar', 'gz',

		// Documents
		'pdf', 'doc', 'docx', 'otf', 'ppt',

		// Images
		'tiff'
	),

	// Max size of file (in bytes)
	// Default: 52428800 (50MB)
	'maxsize' => 52428800,

	/*
	 * Secure URL against fishing?
	 * This passes all links through a downloader to track
	 * and verify the downloads
	 */
	'secureurl' => TRUE,

	/*
	 * Categories become sub-dirs of FILES_PATH,
	 * each can be uploaded to independantly, and can have
	 * unique settings/permissions
	 */
	'cats' => array(
		'publications' => array(
			'title' => 'Publications',

			/*
			 * Restrict permissions on download?
			 * Requires user module; secureurl must be enabled.
			 * Set to user permission flag required
			 */
			'restrict' => U_LOGIN
		),

		'members' => array(
			'title' => 'Member Forms',
			'restrict' => U_LOGIN
		),
	),
);

// Paths
define('FILES_PATH', RPATH.'uploads/files/');
define('FILES_URL', BASEURL.'uploads/files/');

define('FILES_U_ADMIN', U_ADMIN);

// EOF
