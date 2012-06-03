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
			$ret .= '<a href="'.$url.'">'.$linkname.'</a>';
			$ret .= ' <span>'.$sep.'</span> ';
		}
		// Display current url as bold
		else $ret .= '<strong>'.$linkname.'</strong>';
	}

	$ret .= '
	</div>'."\n";

	return $ret;
}

/*
 * Returns debug info to template
 */
function t_debug() {
	global $cfg;

	$html = '<!-- Time: '.(microtime(TRUE)-$cfg['start_time']).'s -->';

	// Display SQL debug if enabled
	if ($cfg['sql_enable']) {
		$html .= "\n".'<!-- SQL #: '.$cfg['sql']['count'].' -->';
	}

	return $html;
}


/*
 * <TWCMS>
 * Silly utility functions for forms
 * Makes code more readable
 *
 * $err = Array of errors
 * $key = Checks for this key inside $err
 */
function t_iserror($err, $key) {
	if (isset($err[$key])) print ' class="error"';
}

/*
 * <TWCMS>
 * Utility function for select drop-downs
 *
 * $opts = array('CA' => 'California');
 * Result: <option value="CA">California</option>
 *
 * $select: Which "value" to mark as selected
 */
function t_select($opts, $select = '') {
	foreach ($opts AS $val => $name) {
		print '
		<option value="'.$val.'"'.($select === $val ? ' selected="selected"' : '')
			.'>'.$name.'</option>';
	}
}

// EOF