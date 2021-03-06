<?php
/*
 * TWCMS 1.x
 *
 * Internal functions used in the core of TWCMS
 *
 * All functions here are fundamental to
 * the internals of TWCMS
 */

if (!defined('SECURITY')) exit;

/*
 * <TWCMS>
 * Loads specified TWCMS module (if enabled),
 * and calls initial load function
 */
function tw_loadmod($mod) {
	global $cfg;

	// Escape $mod name for paths just in case
	$mod = path_escape($mod);

	// Make sure module is usable
	if (!tw_ismod($mod)) return FALSE;

	// Make sure mod has not already been loaded
	if (isset($cfg['mods_loaded'][$mod])) return TRUE;

	// Include library
	// SECURITY: Should be include safe
	require MPATH.$mod.'/'.$mod.'.inc.php';

	// Load config file, which auto merges with global $cfg
	tw_loadcfg($mod);

	// Mark module as loaded
	$cfg['mods_loaded'][$mod] = TRUE;

	return TRUE;
}

/*
 * <TWCMS>
 * Checks all loaded modules for specified event
 *
 * $func: Name of event functions
 * $mod: If specified, it only runs event for that one mod
 * $input: Input value (array for multiple values)
 * Returns: Array of return values; FALSE on failure
 */
function tw_event($func, $mod = FALSE, $input = array()) {
	global $cfg;

	if ($mod === FALSE) {
		$event = array();
		foreach ($cfg['mods_loaded'] AS $mod => $bool) {
			if (function_exists($mod.'_'.$func)) {
				$event[$mod] = call_user_func($mod.'_'.$func, $input);
			}
		}

		return $event;
	}
	elseif (function_exists($mod.'_'.$func)) {
		return array(
			$mod => call_user_func($mod.'_'.$func, $input)
		);
	}

	return FALSE;
}


/*
 * <TWCMS>
 * TW Message function
 * Saves internal messages from event functions
 *
 * Used for cron callbacks and debug
 */
function tw_msg($msg, $file = __FILE__, $line = __LINE__, $newline = TRUE) {
	$file = basename($file);

	print '['.$file.':'.$line.'] '.$msg;

	if ($newline) print "\n";
}

/*
 * <TWCMS>
 * Load config file for specified module
 * and return config array, while merging with global config
 *
 * $merge: Automatically merge new config with global config
 * Format: $cfg[<modname>] = loadcfg(<modname>)
 *
 * Returns full config array for specified module,
 * or empty array if non is set.
 * Returns FALSE if config file could not be found.
 */
function tw_loadcfg($mod, $merge = TRUE) {
	// Load config file for this module
	if (is_file(MPATH.$mod.'/'.$mod.'.cfg.php')) {
		$gcfg = $GLOBALS['cfg'];
		require MPATH.$mod.'/'.$mod.'.cfg.php';

		if ($merge) {
			$GLOBALS['cfg'][$mod] = isset($cfg) ? $cfg : array();
		}

		return isset($cfg) ? $cfg : array();
	}

	return FALSE;
}

/*
 * <TWCMS>
 * Check if mod is loaded
 *
 * Really, this just checks $cfg['mods_loaded']
 * but it makes for a sensible shortcut
 */
function tw_isloaded($mod) {
	global $cfg;

	return isset($cfg['mods_loaded'][$mod]);
}

/*
 * <TWCMS>
 * Checks if specified module is enabled
 * and is available for loading
 *
 * Important: Assumes $mod is safe
 * Should be escaped with path_escape() before call
 */
function tw_ismod($mod) {
	global $cfg;

	// Makes sure mod is in mods_enabled
	if (array_search($mod, $cfg['mods_enabled']) === FALSE) {
		return FALSE;
	}

	// Make sure file exists
	if (!is_file(MPATH.$mod.'/'.$mod.'.inc.php')) return FALSE;

	return TRUE;
}

/*
 * <TWCMS>
 *
 * This handles all email arrays from configs
 * Used in main system and modules
 *
 * $mail - Array of settings
 * Key Settings:
 * 'enable' - Set to FALSE to disable email in config. (Default: TRUE)
 * 'to' - Array or string of who to send the email to
 * 'body' - Body Template
 * 'subject' - Full subject of email
 * 'headers' - Additional email headers
 * 'date' - Date format for map replacement
 * $map - Array of replacements for mail template (See map_replace in utility)
 */
function tw_sendmail($mail, $map = array()) {
	global $cfg;

	// Check if disabled in config
	// Use of 'enable' is legacy in TWCMS
	// if 'enable' doesn't exist, it assumes TRUE
	if (isset($mail['enable']) && !$mail['enable']) return FALSE;

	// Make sure we know who to send to, otherwise nothing to do
	if (!isset($mail['to'])) return FALSE;

	// Date format; use format from $mail if available
	$df = isset($mail['date']) ? $mail['date'] : $cfg['email_date'];

	$map = array_merge($map, array(
		'sitename' => SITENAME,
		'domain' => DOMAIN,
		'url' => FULLURL,
		'wwwurl' => WWWURL,
		'sslurl' => SSLURL,
		'baseurl' => BASEURL,
		'version' => VERSION,
		'date' => date($df, NOW)
	));

	// Generate body text, remove tabs leftover from config
	$temp = str_replace("\t", '', $mail['body']);
	$body = map_replace($map, $temp);

	// Replace subject and headers too
	$subj = map_replace($map, $mail['subject']);
	$head = isset($mail['headers']) ? map_replace($map, $mail['headers']) : '';

	$to = is_array($mail['to']) ? implode(',', $mail['to']) : $mail['to'];

	// Save email to DB if enabled and sql module is loaded
	if ($cfg['email_savedb'] && tw_isloaded('sql')) {
		sql_query('INSERT INTO email ($keys) VALUES ($vals)',
			array(
				'to' => $to,
				'subject' => $subj,
				'body' => $body,
				'headers' => $head,
				'date' => NOW,
				'flags' => 0
			), __FILE__, __LINE__);
	}

	// Send mail and return status
	return mail($to, $subj, $body, $head);
}

/*
 * <TWCMS>
 * Hashes a string based on config
 *
 * Returns computed hash string
 * $salt_str returns the salt by ref
 */
function tw_genhash($input, $salt = FALSE, &$salt_str = '') {
	global $cfg;

	// Add salt encryption
	if ($salt) {
		// A completely over-the-top salt string
		$salt_str = crypt($input, '$5$'.str_shuffle(base64_encode(mt_rand())).'$');
		$input .= $salt_str;
	}

	return hash($cfg['hash_algo'], $input);
}

/*
 * <TWCMS>
 * Compare hash generated with tw_genhash
 * Returns TRUE if $input and $salt_str are
 * the same as in tw_genhash
 *
 * $input - Raw entered password
 * $enc - Encrypted password
 */
function tw_chkhash($input, $enc, $salt_str = '') {
	global $cfg;

	// Add salt to input
	$input .= $salt_str;

	// If computed hash matches save hash; TRUE
	return hash($cfg['hash_algo'], $input) === $enc;
}

/*
 * <TWCMS>
 * Generates Security Token
 * Uses combination of data points to generate security token
 * Important for verifying users across forms, pages, or sessions
 *
 * $key: Starting key to merge token with
 */
function tw_token($key = '') {
	$key .= USERIP;
	$key .= escape($_SERVER['HTTP_USER_AGENT']);
	return tw_genhash($key);
}

/*
 * <TWCMS>
 * Encrypts a string based on config
 * (This is two-way encryption, for one-way see tw_genhash)
 */
function tw_enc($input) {
	global $cfg;

	// Add padding to input, for compatbility with PKCS #7
	$block = mcrypt_get_block_size($cfg['enc_algo'], 'ecb');
	$pad = $block - (strlen($input) % $block);
	$input .= str_repeat(chr($pad), $pad);

	return mcrypt_encrypt($cfg['enc_algo'], $cfg['enc_key'],
			$input, MCRYPT_MODE_ECB);
}

/*
 * <TWCMS>
 * Decrypts string encrypted with tw_enc
 */
function tw_dec($input) {
	global $cfg;

	$input = mcrypt_decrypt($cfg['enc_algo'], $cfg['enc_key'],
			$input, MCRYPT_MODE_ECB);

	// Handle byte padding
	$block = mcrypt_get_block_size($cfg['enc_algo'], 'ecb');
	$pad = ord($input[($len = strlen($input)) - 1]);
	return substr($input, 0, strlen($input) - $pad);
}

/*
 * Generate Receipt # from ID
 *
 * May seem complicated but it's not.
 * The first half is just a short md5 of the week/year it was created,
 * and the second half is the id number padded
 */
function tw_receipt($id, $date = 0) {
	global $cfg, $U;

	if ($date === 0) $date = NOW;

	$date = strtoupper(substr(md5(date('WY', $date)), 4, 4));
	return $date.'-'.str_pad($id, 4, '0', STR_PAD_LEFT);
}

// EOF
