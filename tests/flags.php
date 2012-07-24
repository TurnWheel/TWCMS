#!/usr/bin/php
<?php
define('SECURITY', TRUE);
require '../lib/utility.inc.php';

define('T_ZERO', 0);
define('T_FIRST', 1);
define('T_SECOND', 2);
define('T_THIRD', 4);
define('T_FOURTH', 8);
define('T_LAST', 256);

$small = 3;
$another = 15;
$something = 25;
$more = 29;
$large = 259;

print 'Is $small T_ZERO? '.(hasflag($small, T_ZERO) ? 'TRUE' : 'FALSE')."\n";

print 'Is $large T_ZERO? '.(hasflag($small, T_ZERO) ? 'TRUE' : 'FALSE')."\n";

print 'Is $small T_FIRST? '.(hasflag($small, T_FIRST) ? 'TRUE' : 'FALSE')."\n";

print 'Is $small T_LAST? '.(hasflag($small, T_LAST) ? 'TRUE' : 'FALSE')."\n";

print 'Is $small T_SECOND? '.(hasflag($small, T_SECOND) ? 'TRUE' : 'FALSE')."\n";

print 'Is $large T_FOURTH? '.(hasflag($large, T_FOURTH) ? 'TRUE' : 'FALSE')."\n";

print 'Is $large T_LAST? '.(hasflag($large, T_LAST) ? 'TRUE' : 'FALSE')."\n";

print "\n\n";

print 'rmflag(29, 4) -> 25: '.rmflag(29, 4)."\n";
print 'rmflag(25, 4) -> 25: '.rmflag(25, 4)."\n";


print 'addflag(25, 4) -> 29: '.addflag(25, 4)."\n";
print 'addflag(29, 4) -> 29: '.addflag(29, 4)."\n";

?>
