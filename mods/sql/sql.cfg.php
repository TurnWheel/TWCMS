<?php
/*
 * TWCMS <Module>
 *
 * SQL Module Settings
 */

$cfg = array(
	/* SQL Server Login Details */

	// Host name for SQL Database
	// 'localhost' works for most installations
	'sql_host' => 'localhost',

	// SQL Username (Should NOT be 'root')
	'sql_user' => 'somewebsite',

	// Password for SQL User
	'sql_pass' => 'SQLPass123',

	// Name of MySQL DB To connect to
	'sql_name' => 'somewebsite',
);

/*
 * SQL Global Params
 * These defaults do not change
 */
$cfg['sql'] = array(
	'id' => 0, // Stores current query reference
	'time' => 0, // Stores total execution time
	'count' => 0, // Counts number of queries
	'qstats' => array() // Keeps record of stats
);

// Prefix for SQL tables
define('SQL_PREFIX', '');

// EOF
