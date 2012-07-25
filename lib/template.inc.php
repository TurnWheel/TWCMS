<?php
/*
 * TWCMS 1.x
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
	<nav id="breadcrumbs">'."\n";

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
	</nav>'."\n";

	return $ret;
}

/*
 * Gets debug information from all modules
 * $html: FALSE to disable HTML response, instead returns TEXT only
 *
 * $return: Returns HTML from function if TRUE
 * otherwise the debug info will be saved to $T['debug']
 */
function t_debug($html = TRUE, $return = FALSE) {
	global $cfg, $T;

	// Run 'debug' mod event
	$debug = tw_event('debug');

	if (!$debug) $debug = array();

	// End processing timer
	$debug['globaltimer'] = (microtime(TRUE)-$cfg['start_time']).'s';
	$ret = '';

	foreach ($debug AS $name => $text) {
		if ($html) $ret .= '<!-- ';

		$ret .= $name.': '.$text;

		if ($html) $ret .= ' -->';

		$ret .= "\n";
	}

	if ($return) return $ret;
	$T['debug'] = $ret;
}


/*
 * <TWCMS>
 * Silly utility functions for forms
 * Makes code more readable
 *
 * $err = Array of errors
 * $key = Checks for this key inside $err
 * $return = Print or return html? (Optional)
 */
function t_iserror($err, $key, $return = FALSE) {
	if (isset($err[$key])) {
		if ($return) return ' class="error"';
		print ' class="error"';
	}
}

/*
 * <TWCMS>
 * Utility function for select drop-downs
 *
 * $opts = array('CA' => 'California');
 * Result: <option value="CA">California</option>
 *
 * $select: Which "value" to mark as selected
 *
 * $return: Return or print? Default FALSE (prints)
 */
function t_select($opts, $select = '', $return = FALSE) {
	$html = '';
	foreach ($opts AS $val => $name) {
		$html .= '
		<option value="'.$val.'"'.($select == $val ? ' selected="selected"' : '')
			.'>'.$name.'</option>';
	}

	if ($return) return $html;
	print $html;
}

// EOF
