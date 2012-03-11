<?php
/*
 * TurnWheel CMS
 * Functions used during process script
 * also used by other libraries
 *
 * All functions here are fundamental to
 * the internals of TWCMS
 */


/*
 * <TWCMS>
 * Loads any error page in CPATH/error.<num>.html
 * Returns FALSE on faliure
 */
function p_showerror($num) {
	global $cfg, $T;

	// Escape input just in case
	$num = !is_int($num) ? path_escape($num) : (int) $num;
	$file = CPATH.'error.'.$num.'.html';

	if (!isset($cfg['httpCodes'][$num])) return FALSE;
	if (!file_exists($file)) return FALSE;

	header('HTTP/1.1 '.$cfg['httpCodes'][$num]);

	// Load HTML
	$html = p_htmlfile($file);

	$T['content'] = $html['content'];
	$T['title'] = $T['header'] = $html['header'];

	return TRUE;
}

/*
 * <TWCMS>
 * Loads HTML files using TWCMS data format
 *
 * First line of html files is the header+title
 * Rest of the file is printed out as-is
 *
 * IMPORTANT: Assumes $file to be include safe (path_escape)
 * and assumes the file has been verified to exist
 */
function p_htmlfile($file) {
	// Get content
	$content = file_get_contents($file);

	// Split Main Content from Header
	$split = explode("\n", $content, 2);

	// Check to make sure data is valid
	if ($content === '' || empty($split)) {
		return FALSE;
	}

	$header = '';

	// Split header and content
	if (count($split) > 1) {
		$header = $split[0];
		$content = $split[1];
	}

	// Strip out HTML from header (tends to sneak in)
	$header = strip_tags($header);

	return array(
		'header' => $header,
		'content' => $content
	);
}

/*
 * <TWCMS>
 * Simple processing function to get CSS/JS files quickly
 * Filename Format: dir/PREFIX.file.ext?timestamp
 * ****
 * Sample: get_exfile('css','subpage.css');
 * File Name Returned: css/<PREFIX>.subpage.css?_=12343425;
*/
function p_exfile($dir, $name) {
	return file_exists($dir.'/'.PREFIX.'.'.$name) ?
			PREFIX.'.'.$name.'?_='.filemtime($dir.'/'.PREFIX.'.'.$name)
			: FALSE;
}

/*
 * <TWCMS>
 * Convert URL segment to full "printable" name
 *
 * Replaces '-' with ' / ', and '_' with spaces
 * then capitalizes each word
 */
function p_url2name($url) {
	return ucwords(str_replace('-',' / ', str_replace('_',' ',$url)));
}

// EOF
