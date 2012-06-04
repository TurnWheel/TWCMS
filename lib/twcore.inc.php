<?php
/*
 * TWCMS 1.0
 *
 * Internal functions used in the core of TWCMS
 *
 * All functions here are fundamental to
 * the internals of TWCMS
 */

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

	// Mark module as loaded
	$cfg['mods_loaded'][$mod] = TRUE;

	// Load config file and merge config into global cfg
	$ncfg = tw_loadcfg($mod);
	if ($ncfg !== FALSE) {
		$cfg = array_merge($cfg, $ncfg);
	}

	// Call onLoad event functions
	tw_runEvent('onLoad', $mod);

	return TRUE;
}

/*
 * <TWCMS>
 * Checks all loaded modules for specified event
 * function and calls if found
 *
 * If $mod is specified, it only runs the event for that mod
 */
function tw_runEvent($func, $mod = FALSE) {
	global $cfg;

	if ($mod === FALSE) {
		foreach ($cfg['mods_loaded'] AS $mod => $bool) {
			if (function_exists($mod.'_'.$func)) {
				call_user_func($mod.'_'.$func);
			}
		}

		return TRUE;
	}
	elseif (function_exists($mod.'_'.$func)) {
		call_user_func($mod.'_'.$func);
		return TRUE;
	}

	return FALSE;
}

/*
 * <TWCMS>
 * Load config file for specified module
 * and return config array
 */
function tw_loadcfg($mod) {
	// Load config file for this module
	if (file_exists(MPATH.$mod.'/'.$mod.'.cfg.php')) {
		require MPATH.$mod.'/'.$mod.'.cfg.php';
		return $cfg;
	}

	return FALSE;
}

/*
 * <TWCMS>
 * Check if mod is loaded
 *
 * Really, this just check $cfg['mods_loaded']
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

	// Makes sure it is in mods_avail
	if (array_search($mod, $cfg['mods_avail']) === FALSE) {
		return FALSE;
	}

	// If _enable flag is available, and it is FALSE, it is disabled
	// If no _enable flag is present, it proceeds as if it were TRUE
	if (isset($cfg[$mod.'_enable']) && !$cfg[$mod.'_enable'])  {
		return FALSE;
	}

	// Make sure file exists
	if (!file_exists(MPATH.$mod.'/'.$mod.'.inc.php')) return FALSE;

	return TRUE;
}


/*
 * <TWCMS>
 * Hashes a string based on config
 * settings, set in config.inc.php
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
 * Encrypts a string based on config
 * settings, set in config.inc.php
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
