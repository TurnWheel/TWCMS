<?php
/*
 * TWCMS <Module>
 *
 * Processes download requests without revealing urls
 */

$cat = isset($H['cat']) ? path_escape($H['cat']) : '';
$file = isset($H['fn']) ? path_escape($H['fn']) : '';

// If input not specified, throw 404 error
if ($file === '' || $cat === '') {
	p_showerror(404);
	print 'error1';
	return FALSE;
}

// Restrict permissions if enabled for this category
if (files_restrict($cat)) {
	p_showerror(403);
	return FALSE;
}

/*
 * Handles URL values that end up in the file name
 */
$file = urldecode($file);

if (!files_print($cat, $file)) {
	p_showerror(404);
	return;
}

// EOF
