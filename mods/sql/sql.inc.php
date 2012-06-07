<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 1.0
 * Author: Steven Bower
 * TurnWheel Designs (cc) 2012
 *
 * Simple MySQL Wrapper
 */

if (!defined('SECURITY')) exit;

// SQL Global Params
$cfg['sql'] = array(
	'id' => 0, // Stores current query reference
	'time' => 0, // Stores total execution time
	'count' => 0, // Counts number of queries
	'qstats' => array() // Keeps record of stats (DEBUG MODE ONLY)
);

/*
 * Constructor
 * Simply calls sql_connect with proper cfg params
 */
function sql_onLoad() {
	global $cfg;

	// Connect to SQL Server
	sql_connect($cfg['sql_host'], $cfg['sql_user'],
		$cfg['sql_pass'],$cfg['sql_name']);

	// Security Measure: Don't keep user/pass set
	unset($cfg['sql_user'], $cfg['sql_pass']);

	return TRUE;
}

// Creates MySQL Connection
function sql_connect($host, $user, $password = '', $name = '') {
	global $cfg;

	// Verify Credentials and connect
	if (!$cfg['sql']['con'] = mysql_pconnect($host, $user, $password)) {
		sql_error('Could not connect to MySQL Server'
					.' (Host: '.$host.' | User: '.$user.')', TRUE);
	}

	// Select Database
	if ($name !== '' && !mysql_select_db($name)) {
		mysql_close($cfg['sql']['con']);
		sql_error('Database could not be selected (DB: '.$name.')', TRUE);
	}

	// Return Connection
	return $cfg['sql']['con'];
}

// Close MySQL Connection
function sql_close() {
	global $cfg;

	// See if we need to free existing mysql query
	if ($cfg['sql']['con']) {
		if ($cfg['sql']['id']) @mysql_free_result($cfg['sql']['id']);

		return mysql_close($cfg['sql']['con']);
	}
	else return FALSE;
}

/*
 * Prepares sql statement with parameter replacements
 */
function sql_prepare($q, $vals) {
	// Escape vals if it is a string or array
	if (is_string($vals) || is_array($vals)) {
		$vals = escape($vals);
	}

	/*
	 * Handle arrays with special case, as they
	 * can be used in multiple ways
	 *
	 * Example: array('joe', 1234) will simply replace in order
	 *
	 * Valued arrays can also be used:
	 * array('name' => 'joe', 'flag' => 1234);
	 *
	 * Valued arrays give you access to $keys and $vals, which
	 * can be used for simple INSERT functions
	 *
	 * Example:
	 * INSERT INTO test ($keys) VALUES($vals)
	 * and just provide an 'key' => 'val' format array
	 */
	if (is_array($vals)) {
		// Process $keys if requested
		if (strpos($q, '$keys') !== FALSE) {
			$keys = array_keys($vals);

			// Add backticks to keys
			foreach ($keys AS $k => $v) {
				$keys[$k] = '`'.$v.'`';
			}

			// Replace "$keys" var with keys of $vals array
			$q = str_replace('$keys', implode($keys, ', '), $q);
		}

		// Don't get $vals and '$vals' mixed up
		if (strpos($q, '$vals') !== FALSE) {
			// Replace $vals with %s's as a easy shortcut
			$num = sizeof($vals);
			$q = str_replace('$vals', '"%s"'.str_repeat(',"%s"', $num-1), $q);
		}

		// Run sprintf with array of vals
		$q = vsprintf($q, $vals);
	}
	// Handles strings, integers, etc.
	else $q = sprintf($q, $vals);

	return $q;
}

// Process Queries
function sql_query($q, $vals = array(), $file = __FILE__, $line = __LINE__) {
	global $cfg;

	// Generate full query using inputed array
	// Do not use empty() here as it would ignore 0 and '0'
	if ($vals !== '' && $vals !== array()) {
		$q = sql_prepare($q, $vals);
	}

	$cfg['sql']['id'] = 0; // Unset existing ID

	if ($q !== '') {
		// Queries are timed in debug mode
		if ($cfg['debug']) {
			$sqltime = microtime(TRUE);
		}

		// Verify valid query and execute
		if (!$cfg['sql']['id'] = mysql_query($q, $cfg['sql']['con'])) {
			sql_error('<strong>Bad SQL Query</strong> ('.$file.':'.$line.'):
						'.htmlentities($q).'<br />
						<strong>'.mysql_error().'</strong>');
		}

		// Tracks query count (always, regardless of debug)
		++$cfg['sql']['count'];

		// Track all stats in debug mode
		if ($cfg['debug']) {
			$sqltime = microtime(TRUE)-$sqltime;

			$cfg['sql']['time'] += $sqltime;
			$cfg['sql']['qstats'][] = array(
				'query' => str_replace("\t", '', htmlentities($q)),
				'time' => $sqltime,
				'file' => $file,
				'line' => $line
			);
		}

		// Return query reference
		return $cfg['sql']['id'];
	}
}

// Fetch associative array
function sql_fetch_array($id = -1) {
	global $cfg;

	if ($id !== -1) $cfg['sql']['id'] = $id;
	return mysql_fetch_assoc($cfg['sql']['id']);
}

// See php.net/mysql_data_seek
function sql_data_seek($n, $id = -1) {
	global $cfg;

	if ($id !== -1) $cfg['sql']['id'] = $id;
	mysql_data_seek($cfg['sql']['id'], $n);
}

// Find next ID value from
// Column 'c' inside table 't'
function sql_nextid($c, $t) {
	sql_query('SELECT MAX('.$c.') AS max FROM '.$t);
	$r = sql_fetch_array();
	$r['max'] = (int) $r['max'];

	return (($r['max']+1) > 0) ? $r['max']+1 : 1;
}

// Get recent insert ID
function sql_insert_id($id = -1) {
	global $cfg;

	return ($id === -1) ? mysql_insert_id() : mysql_insert_id($id);
}

// Free result
function sql_free_result($id = -1) {
	global $cfg;

	if ($id !== -1) $cfg['sql']['id'] = $id;
	return mysql_free_result($cfg['sql']['id']);
}

// Return number of rows
function sql_num_rows($id = -1) {
	global $cfg;

	if ($id !== -1) $cfg['sql']['id'] = $id;
	return mysql_num_rows($cfg['sql']['id']);
}

// Return MySQL Error
function sql_error($err, $halt = FALSE) {
	global $cfg;

	trigger_error($err,E_USER_ERROR);
	if ($halt) exit;
}

// EOF
