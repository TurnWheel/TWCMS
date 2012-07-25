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

/*
 * TW Event Function
 * Simply calls sql_connect with proper cfg params
 */
function sql_onLoad() {
	global $cfg;

	// Connect to SQL Server
	sql_connect($cfg['sql_host'], $cfg['sql_user'],
		$cfg['sql_pass'],$cfg['sql_name']);

	return TRUE;
}

/*
 * TW Event Function
 * Saves all debug information to $T['debug'] template var
 */
function sql_debug() {
	global $cfg, $T;

	$ret = 'Queries; '.$cfg['sql']['count'].';';
	$ret .= 'SQL Time; '.$cfg['sql']['time'];

	if (!empty($cfg['sql']['qstats'])) {
		$ret .= "\n\n".htmlentities(print_r($cfg['sql']['qstats'], TRUE));
	}

	return $ret;
}

/*
 * Creates MySQL Connection
 * and returns sql connection resource
 */
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

/*
 * Close MySQL Connection
 */
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
 * with support for single strings and arrays
 *
 * All values are automatically escaped for MySQL input
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

/*
 * Executes query and saves execution stats
 * as well as track errors (with file/line information)
 *
 * $vals can be an array of values, key/value pair,
 * or just a single string/number
 *
 * Returns query resource id
 */
function sql_query($q, $vals = array(), $file = __FILE__, $line = __LINE__) {
	global $cfg;

	// Generate full query using inputed array
	// Do not use empty() here as it would ignore 0 and '0'
	if ($vals !== '' && $vals !== array()) {
		$q = sql_prepare($q, $vals);
	}

	$cfg['sql']['id'] = 0; // Unset existing ID

	if ($q !== '') {
		$sqltime = microtime(TRUE);

		// Execute query, save to resource global
		// and check for errors
		if (!$cfg['sql']['id'] = mysql_query($q, $cfg['sql']['con'])) {
			sql_error('<strong>Bad SQL Query</strong> ('.$file.':'.$line.'):
						'.htmlentities($q).'<br />
						<strong>'.mysql_error().'</strong>');
		}

		// Save all query stats
		$cfg['sql']['count'] += 1;
		$sqltime = microtime(TRUE)-$sqltime;

		$cfg['sql']['time'] += $sqltime;
		$cfg['sql']['qstats'][] = array(
			'query' => $q,
			'time' => $sqltime,
			'file' => $file,
			'line' => $line
		);

		// Return query reference
		return $cfg['sql']['id'];
	}
}

/*
 * SQL Query Tracking
 *
 * Starts tracking queries for different purposes
 * If tracking has already started, it will get reset!
 */
function sql_track_start() {
	global $cfg;

	$cfg['sql']['track_start'] = sizeof($cfg['sql']['qstats']);
	return TRUE;
}

/*
 * SQL Query Tracking
 *
 * Ends tracking
 * Will re-do all queries processed from start of tracking
 * with the new DB settings. If no DB settings are entered,
 * it just returns array of query information
 *
 * DB Settings Expected Array format:
 * array(
 * 'host' => 'localhost',
 * 'user' => 'dbuser',
 * 'pass' => 'pass',
 * 'name' => 'db2'
 * );
 */
function sql_track_end($db = FALSE) {
	global $cfg;

	$stats = $cfg['sql']['qstats'];
	$end = sizeof($stats);
	$start = $cfg['sql']['track_start'];

	// End tracking regardless
	unset($cfg['sql']['track_start']);

	$diff = $end-$start;

	// If there have not been queries, return FALSE
	if ($diff <= 0) return FALSE;

	$queries = array_slice($stats, $end-$diff);

	// If no DB information is entered, it just returns queries
	if (!is_array($db) || $db === array()) {
		return $queries;
	}
	// If DB information is present, try to connect to new information,
	// and re-run all queries
	else {
		sql_connect($db['host'], $db['user'], $db['pass'], $db['name']);

		foreach ($queries AS $key => $qinfo) {
			sql_query($qinfo['query']);
		}

		// Re-connect to default DB
		sql_onLoad();
	}
}

/*
 * Returns associative array
 * Most commonly used method
 */
function sql_fetch_array($id = -1) {
	global $cfg;

	if ($id !== -1) $cfg['sql']['id'] = $id;
	return mysql_fetch_assoc($cfg['sql']['id']);
}

/*
 * See php.net/mysql_data_seek
 */
function sql_data_seek($n, $id = -1) {
	global $cfg;

	if ($id !== -1) $cfg['sql']['id'] = $id;
	mysql_data_seek($cfg['sql']['id'], $n);
}

/*
 * Find next ID value to be populated from
 * Column $col inside table $tbl
 *
 * This is essentially a PHP version of auto_increment,
 * except it must be called manually
 */
function sql_nextid($col, $tbl) {
	sql_query('SELECT MAX('.$col.') AS max FROM '.$tbl, '', __FILE__, __LINE__);
	$r = sql_fetch_array();
	$max = intval($r['max'])+1;

	return $max > 0 ? $max+1 : 1;
}

/*
 * Gets auto_increment ID of latest INSERT query
 */
function sql_insert_id($id = -1) {
	global $cfg;

	return ($id === -1) ? mysql_insert_id() : mysql_insert_id($id);
}

/*
 * Frees up memory from query
 */
function sql_free_result($id = -1) {
	global $cfg;

	if ($id !== -1) $cfg['sql']['id'] = $id;
	return mysql_free_result($cfg['sql']['id']);
}

/*
 * Returns number of rows returned in query
 * Recommended to avoid this function, as it is considered
 * very slow and inefficient
 */
function sql_num_rows($id = -1) {
	global $cfg;

	if ($id !== -1) $cfg['sql']['id'] = $id;
	return mysql_num_rows($cfg['sql']['id']);
}

/*
 * Triggers sql errors
 * $halt bool determines if script should exit
 */
function sql_error($err, $halt = FALSE) {
	global $cfg;

	trigger_error($err, E_USER_ERROR);

	if ($halt) exit;
}

// EOF
