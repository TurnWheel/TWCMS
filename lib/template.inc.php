<?php
/*
 * TWCMS 1.0
 * Template Library
 *
 * Useful template based functions
 * Should be the only library with HTML (rare exceptions)
 *
 * These functions are not fundamental to TWCMS
 * as they are only used in index.php template or content files,
 * and can be easily replaced or removed if desired
 */

if (!defined('SECURITY')) exit;

/*
 * Breadcrumb function
 *
 * Simple: Call with $T['bcrumbs'] array
 * separated by $sep
 */
function t_bcrumbs($bcrumbs, $sep = '&gt;') {
	// If no input, just return empty string
	if (empty($bcrumbs)) return '';

	// Set current url to VAR for purpose of this function
	$currurl = CURRURL;

	$ret = '
	<div id="breadcrumbs">'."\n";

	foreach ($bcrumbs AS $name => $url) {
		// Convert url to printable name
		$linkname = p_url2name($name);

		// Display link if does not match current URL
		// (checks for trailing slash as well)
		if ($url !== $currurl && $url.'/' !== $currurl) {
			$ret .= '<a href="'.$url.'">'.$linkname.'</a> &gt; ';
		}
		// Display current url as bold
		else $ret .= '<strong>'.$linkname.'</strong>';
	}

	$ret .= '
	</div>'."\n";

	return $ret;
}
