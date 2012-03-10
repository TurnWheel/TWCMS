#!/usr/bin/php
<?php
define('SECURITY', TRUE);
$cfg = array(
	/* Database Settings */
	'sql_enable' => FALSE, // Is DB connection even required?
);

require '../lib/security.inc.php';
require '../lib/sql.inc.php';

// Testing SQL query structure in the context of a fake contact form
// All inputs are simulated with $data
$fields = array(
	'name','email','phone','message'
);

$data = array(
	'name' => 'Test Name',
	'email' => 'test@example.com',
	'phone' => '555-5555-1055',
	'message' => 'This is just another message'
);

print sql_prepare('INSERT INTO contact ($keys) VALUES($vals)', $data);

print "\n\n";

$id = 5;
print sql_prepare('SELECT * FROM user WHERE userid = "%d"', $id);

print "\n\n";

// EOF
?>
