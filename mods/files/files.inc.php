<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 0.6
 * Author: Steven Bower
 * BPWebDesign (cc) 2012
 *
 * File upload wrapper
 * Makes managing of files uploads simpler and easy to include
 * on specific pages
 */

if (!defined('SECURITY')) exit;

/***
 * EVENTS
 ***/

/* Adds link to admin area */
function files_adminMenu() {
	return array(
		'url' => '/admin/files/',
		'text' => 'Manage Resource Files'
	);
}

/***
 * Front-End
 ***/

/*
 * List all files in specified directory
 * Returns FALSE if permission is restricted
 */
function files_list($dir) {
	global $cfg;

	// Make sure permission is allowed to this dir
	if (files_restrict($dir)) {
		return FALSE;
	}

	$path = FILES_PATH.files_dir($dir).'/';

	$files = array();
	$s = $cfg['files']['secureurl'];

	foreach (glob($path.'*.*') AS $file) {
		$fn = basename($file);

		$files[] = array(
			'name' => $fn,
			'size' => filesize($file),
			'url' => $s ? '/files/cat:'.$dir.'/fn:'.$fn.'/'
				: FILES_URL.$dir.'/'.$fn
		);
	}

	return $files;
}

/*
 * Downloads file directly to browser
 * with all HTTP headers included
 */
function files_print($dir, $fn) {
	// Verify input
	$dir = files_dir($dir);
	if (!files_isfile($dir, $fn)) {
		return FALSE;
	}

	$path = FILES_PATH.$dir.'/'.$fn;

	$size = filesize($path);
	$mime = files_type($path);
	$file = file_get_contents($path);

	header('Content-Description: File Transfer');
	header('Content-type: '.$mime);
	header('Content-Disposition: attachment; filename="'.$fn.'"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Length: '.$size);

	print $file;
	exit;
}

/***
 * Admin Functions
 ***/

/*
 * Process and save files from a form into specified DIR
 */
function files_process($files, $dir) {
	global $cfg;

	// Verify input
	if ($dir === '') {
		return FALSE;
	}

	$dir = files_dir($dir);
	$path = FILES_PATH.$dir.'/';

	// Tracks which files should be saved,
	// and which are successfully saved
	$save = $status = array();

	foreach ($files['error'] AS $key => $error) {
		if ($error === UPLOAD_ERR_OK) {

			// Verify image settings
			// TODO: Verify EXT
			$ext = $files['type'][$key];
			$size = $files['size'][$key];

			if ($size <= $cfg['upload']['maxsize']) {
				$save[] = array(
					'name' => $files['name'][$key],
					'path' => $files['tmp_name'][$key],
					'ext' => $ext,
				);
			}
		}
	}

	// If nothing saved, just exit
	if (sizeof($save) === 0) {
		return TRUE;
	}

	// Make sure there is a directory for these images
	if (!is_dir($path)) {
		mkdir($path);
	}

	// Rename files and resize
	foreach ($save AS $key => $info) {
		$from = $info['path'];
		$to = $path.'/'.$info['name'];

		$status[$key] = move_uploaded_file($from, $to) ? TRUE : FALSE;
	}

	return $status;
}

/*
 * Deletes specified file
 */
function files_rm($dir, $fn) {
	// Verify inputs
	if ($dir === '' || $fn === '') {
		return FALSE;
	}

	$dir = files_dir($dir);
	if (!files_isfile($dir, $fn)) {
		return FALSE;
	}

	return unlink(FILES_PATH.$dir.'/'.$fn);
}

/***
 * Utility Functions
 ***/

/*
 * Are the contents of this category restricted?
 * Returns FALSE by default, even if category is not specified
 * Requires user module
 */
function files_restrict($cat) {
	global $cfg;

	if (isset($cfg['files']['cats'][$cat])) {
		$restrict = $cfg['files']['cats'][$cat]['restrict'];

		if ($restrict !== FALSE && !user_hasperm($restrict)) {
			return TRUE;
		}
	}

	return FALSE;
}


/*
 * Get file type
 */
function files_type($path) {
	$input = escapeshellarg($path);
	$output = shell_exec('file -i -b '.$input);
	$split = explode(';', $output);

	return $split[0];
}

/*
 * Utility: Does file exist in dir?
 * Assumes dir has already been run through files_dir
 */
function files_isfile($dir, $fn) {
	return is_file(FILES_PATH.$dir.'/'.$fn);
}

/*
 * Utility: Get dir hash name format
 */
function files_dir($dir) {
	return $dir.'-'.substr(sha1($dir), 1, 8);
}

// EOF
