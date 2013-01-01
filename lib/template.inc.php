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
 * <TWCMS>
 * Adds a header resource to the template
 *
 * $type: 'css' or 'js'
 * $name: File basename (without extension or path)
 * $attr: Additional attributes and settings
 * $priority: 1-10, 1 being highest priority, determins order
 * $prefix: (optional) PREFIX is automatically added to CSS/JS file names
 */
function t_addRes($type, $name, $pri = 10, $prefix = PREFIX) {
	global $T;

	$pri = (int) $pri;
	// Sanity check on priority input
	if ($pri > 10 || $pri < 1) {
		$pri = 10;
	}

	if ($type === 'css' || $type === 'js') {
		$file = t_exfile($type, $name.'.'.$type, $prefix);

		if (!$file) return FALSE;

		if (!isset($T[$type])) {
			$T[$type] = array_fill(1, 10, array());
		}

		$T[$type][$pri][$name] = $file;

		return TRUE;
	}

	return FALSE;
}

/*
 * <TWCMS>
 * Displays resources inside template
 * Resources are defined by t_addRes
 *
 * $type: Must be either CSS or JS
 */
function t_displayRes($type = 'css') {
	global $T;

	if (!isset($T[$type])) return FALSE;

	$html = '';

	foreach ($T[$type] AS $pri => $name) {
		foreach ($name AS $file) {
			if (!is_string($file) || empty($file)) continue;

			$html .= "\t";

			if ($type === 'css') {
				$html .= '<link rel="stylesheet" type="text/css"'
					.'href="'.BASEURL.'css/'.$file.'" />';
			}
			elseif ($type === 'js') {
				$html .= '<script src="'.BASEURL.'js/'.$file.'"></script>';
			}

			$html .= "\n";
		}
	}

	return $html;
}

/*
 * <TWCMS>
 * Adds meta data to template array
 */
function t_addMeta($name, $content= '') {
	global $T;

	if (!isset($T['meta'])) $T['meta'] = array();

	$T['meta'][$name] = html_escape($content);

	return TRUE;
}

/*
 * <TWCMS>
 * Displays meta data added with t_addMeta
 *
 * $name: Specifies specific meta value to return,
 * otherwise returns all of them
 */
function t_displayMeta($name = '') {
	global $T;

	$metas = $T['meta'];
	if ($name !== '') {
		if (isset($T['meta'][$name])) {
			$metas = array($name => $T['meta'][$name]);
		}
		else return FALSE;
	}

	$html = '';

	foreach ($metas AS $name => $content) {
		$html .= "\t".'<meta name="'.$name.'" content="'.$content.'" />'."\n";
	}

	return $html;
}

/*
 * <TWCMS>
 * Simple processing function to get CSS/JS files quickly
 * Filename Format: dir/PREFIX.file.ext?timestamp
 * ****
 * Sample: t_exfile('css','subpage.css');
 * File Name Returned: css/<PREFIX>.subpage.css?_=12343425;
*/
function t_exfile($dir, $name, $prefix = PREFIX) {
	$fname = ($prefix ? $prefix.'.' : '').$name;
	$fpath = RPATH.$dir.'/'.$fname;

	return is_file($fpath) ? $fname.'?_='.filemtime($fpath) : FALSE;
}

/*
 * Breadcrumb function
 *
 * Simple: Call with $T['bcrumbs'] array
 * separated by $sep
 */
function t_bcrumbs($bcrumbs, $sep = '&gt;') {
	// If no input, just return empty string
	if (empty($bcrumbs)) return '';

	$ret = '
	<nav id="breadcrumbs">'."\n";

	foreach ($bcrumbs AS $name => $url) {
		// Convert url to printable name
		$linkname = p_url2name($name);

		// Display link if does not match current URL
		// (checks for trailing slash as well)
		if ($url !== CURRURL) {
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
function t_debug($html = TRUE, $return = TRUE) {
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
 * $nokey: If TRUE ignores array keys, and just uses value for both val/name
 */
function t_select($opts, $select = '', $return = FALSE, $nokey = FALSE) {
	$html = '';
	foreach ($opts AS $key => $name) {
		if ($nokey) $key = $name;

		$html .= '
		<option value="'.$key.'"'.($select == $key ? ' selected="selected"' : '')
			.'>'.$name.'</option>';
	}

	if ($return) return $html;
	print $html;
}

// EOF
