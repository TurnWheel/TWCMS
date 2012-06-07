<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 0.5
 * Author: Steven Bower
 * TurnWheel Designs (cc) 2012
 *
 * Error management functions
 *
 * ---- Additional Files ----
 * content/error.php.html
 * content/error.php.debug.html
 * content/admin_errors.inc.php
 * js/<pre>.admin_errors.js
 * css/<pre>.admin_errors.css
 * ----
 */

function error_onLoad() {
	// Define global error handler
	set_error_handler('error_handle');
}

/*
 * Handles all error calls
 */
function error_handle($errno, $errstr, $errfile, $errline, $errcontext) {
	global $cfg;

	// Generate variable dump
	$dump = '';
	foreach ($errcontext AS $k => $v) {
		$dump .= error_parse_dump($v, $k);
	}

	// Map of values for templates (email and html)
	$map = array(
		'error_str' => $errstr,
		'error_num' => $errno,
		'error_name' => $cfg['error_vals'][$errno],
		'error_file' => $errfile,
		'error_line' => $errline,
		'htmldump' => $dump
	);

	// Insert var dump into MySQL DB if enabled
	if ($cfg['error_savedb']) {
		$err_a = array($errstr, $errno, $errfile, $errline);

		sql_query('INSERT INTO error SET date = "%d",error = "%s",dump = "%s"',
						array(NOW, serialize($err_a), serialize($errcontext)),
						__FILE__, __LINE__);

		// Add insert id to replacement map
		$map['error_sqlid'] = sql_insert_id();
	}

	// Display template if enabled
	if ($cfg['error_template']) {
		$file = CPATH.($cfg['debug'] ? 'error.php.debug.html' : 'error.php.html');

		if (file_exists($file)) {
			$content = file_get_contents($file);
			print map_replace($map, $content);
		}
	}

	// Send email
	tw_sendmail($cfg['error_email'], $map);

	// End processing
	exit;
}

/*
 * Parse out a variable dump into readable format
 */
function error_parse_dump($value, $name, $level = 0) {
	// Skip GLOBALS, as it causes too much recursion
	if ($name === 'GLOBALS') return;

	if (is_array($value)) {
		$count = count($value);

		if ($count !== 0) {
			$text = 'array('.$count."\n".'<div class="array">'."\n";

			$i = 0;
			foreach($value as $key2 => $value2) {
				$text .= ' ['.error_parse_val($key2) . '] = '.
					error_parse_dump($value2, '', $level+1).
					(++$i !== $count ? ',' : '') .
					'<br />'."\n";
			}

			$text .= '</div>'."\n".')';
		}
		else $text = 'array()';
	}
	else $text = error_parse_val($value);

	if ($level === 0) {
		$text = '$'.$name.' = '.$text.';<br />'."\n";
	}

	return $text;
}

/*
 * Parse a variable value based on type
 */
function error_parse_val($value) {
	if (is_bool($value)) return $value ? 'TRUE' : 'FALSE';
	if (is_int($value) || is_float($value)) return $value;

	if (is_string($value)) {
		return '"'.html_escape(
				str_replace("\r", '\r',
				str_replace("\n", '\n',
				str_replace("\t", '\t',
				str_replace('"', '\\"',
				str_replace('\\', '\\\\',
				$value)))))).'"';
	}

	if (is_object($value)) return 'Object (Not supported by error handler)';
	if (is_resource($value)) return 'Resource';
	if (is_null($value)) return 'NULL';

	return '<strong>Unable to determine variable type.</strong>';
}

// EOF
