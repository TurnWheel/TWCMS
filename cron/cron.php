<?php
/*
 * TWCMS Cron Jobs
 * Simple wrapper that executes any cron jobs needed by modules
 *
 * Note: Does not run process.inc.php
 * onLoad and onCron are the only events called
 *
 * Only run when necessary
 *
 * TODO: Allow each module to set cron interval
 */

require '../globals.inc.php';

tw_event('onCron');
