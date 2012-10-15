<?php
/*
 * TWCMS Cron Jobs
 * Simple wrapper that executes any cron jobs needed by modules
 *
 * Note: Does not run process.inc.php
 * onLoad, onCron, and debug are the only events called
 *
 * Only run when necessary
 *
 * TODO: Allow each module to set cron interval
 */

require dirname(__FILE__).'/../globals.inc.php';

/*
 * Determines if it is being run from CLI
 * It is allowed to run from web-browser,
 * though normally blocked by .htaccess
 *
 * HTTP access should be for debug purposes only
 */
$cli = !isset($_SERVER['HTTP_HOST']);

if (!$cli) print '<pre>';

// Run cron events and print out return values
$cron = tw_event('onCron');
foreach ($cron AS $mod => $ret) {
	print '['.$mod.']: '.(is_array($ret) ? print_r($ret, TRUE) : $ret);
	print "\n\n";
}

if (!$cli) print '</pre>';

// Load and print all debug information
print t_debug(FALSE);

?>
