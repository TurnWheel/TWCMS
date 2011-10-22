<?php
/*
 * TWCMS 0.9-Beta
 * MySQL Functions
 */

if (!defined('SECURITY')) die('Security Error');

// SQL Global Params
$cfg['sql'] = array(
	'id' => 0, // Stores current query reference
	'time' => 0, // Stores total execution time
	'count' => 0, // Counts number of queries
	'qstats' => array() // Keeps record of stats (DEBUG MODE ONLY)
);

// Creates MySQL Connection
function sql_connect($host, $user, $password = '', $name = '') {
    global $cfg;
	
	// Verify Credentials and connect
    if (!$cfg['sql']['con'] = mysql_pconnect($host, $user, $password)) {
        sql_error('Could not connect to MySQL Server (Host: '.$host.' | User: '.$user.').', TRUE);
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

// Process Queries
function sql_query($q) {
    global $cfg;
	
	$cfg['sql']['id'] = 0; // Unset existing ID

    if ($q !== '') {
        $startsqltime = microtime(TRUE); // Start timer
        
		// Verify valid query and execute
        if (!$cfg['sql']['id'] = mysql_query($q, $cfg['sql']['con'])) {
            sql_error('<strong>Bad SQL Query</strong>: '.htmlentities($q).'<br /><strong>'.mysql_error().'</strong>');
		}
        
		// Track time and query count
        $cfg['sql']['time'] += microtime(TRUE)-$startsqltime;
        ++$cfg['sql']['count'];
		
		// Add stats if in debug mode
        if ($cfg['debug']) {
            $cfg['sql']['qstats'][] = htmlentities($q).'<br /><b>Querytime:</b> '.$cfg['sql']['time'];
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
