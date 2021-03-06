<?php
/*
 * TWCMS 1.x
 *
 * Functions used during process script
 * also used by other libraries
 *
 * All functions here are fundamental to
 * the internals of TWCMS
 */

if (!defined('SECURITY')) exit;

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
	if (!is_file($file)) return FALSE;

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
 * Convert URL segment to full "printable" name
 *
 * Replaces '-' with space, '.' with '-', '~' with ' / '
 * then capitalizes each word.
 *
 * Examples--
 * abbey-road : Abbey Road
 * early.80s~late.80s : Early-80s / Late-80s
 * early-80s-.-late-80s : Early 80s - Late 80s
 */
function p_url2name($url) {
	return ucwords(
		str_replace('~', ' / ',
		str_replace('.', '-',
		str_replace('-', ' ', $url))));
}

/*
 * Returns JSON response with proper headers,
 * and ends execution of script
 */
function p_endjson($array) {
	header('Cache-Control: no-cache, must-revalidate');
	header('Content-type: application/json');
	print json_encode($array);
	exit;
}

// EOF
