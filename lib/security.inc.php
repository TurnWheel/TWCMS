<?php
/*
 * TWCMS 1.0
 *
 * Set of security functions, such as HTTP auth
 * and password encryption methods
 *
 * All functions here are fundamental to
 * the internals of TWCMS
 */

if (!defined('SECURITY')) exit;

/*
 * <TWCMS>
 * Escape headers for general use.
 */
function escape($v) {
	global $cfg;

	// Handle arrays recursively
	if (is_array($v)) {
		foreach ($v AS $k => $aval) {
			$v[$k] = escape($aval);
		}

		return $v;
	}

	return $cfg['sql_enable'] ? mysql_real_escape_string($v)
		: htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

/*
 * <TWCMS>
 * Escape headers for use in includes,
 * and any file-based function
 */
function path_escape($v) {
	return preg_replace('/(\/\.\/)|[\/\\\]|(\.\.)/','', $v);
}

/*
 * <TWCMS>
 * Loads specified TWCMS module (if enabled),
 * and calls initial load function
 */
function tw_loadmod($mod) {
	global $cfg;

	// Escape $mod name for paths just in case
	$mod = path_escape($mod);

	// Make sure mod has not already been loaded
	if (isset($cfg['mods_loaded'][$mod])) return TRUE;

	// Make sure module is usable
	if (!tw_ismod($mod)) return FALSE;

	// Include library
	// SECURITY: Should be include safe
	require LPATH.'mod.'.$mod.'.inc.php';

	// Look for onload function
	if (function_exists($mod.'_onload')) {
		return call_user_func($mod.'_onload');
	}

	// Mark module as loaded
	$cfg['mods_loaded'][$mod] = TRUE;

	return TRUE;
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
	if (!file_exists(LPATH.'mod.'.$mod.'.inc.php')) return FALSE;

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

/*
 * <TWCMS>
 * Request HTTP Auth
 */
function req_auth($realm = 'Secret Realm') {
	header('WWW-Authenticate: Basic realm="'.$realm.'"');
	header('HTTP/1.1 401 Unauthorized');
	print '<h1>Error 401: Authorization Required</h1>';
	exit;
}

/*
 * <TWCMS>
 * Veriy HTTP Auth
 * Input: array('user' => 'me', 'pass' => sha1('test'))
 */
function check_auth($user) {
	// For PHP5 CGI In conjunction
	// with mod_rewrite: E=HTTP_AUTH:%{HTTP:Authorization}
	if (isset($_SERVER['REDIRECT_HTTP_AUTH'])
			&& !empty($_SERVER['REDIRECT_HTTP_AUTH'])) {
		list($type, $content) = explode(' ', $_SERVER['REDIRECT_HTTP_AUTH'], 2);
		list($_SERVER['PHP_AUTH_USER'],
				$_SERVER['PHP_AUTH_PW']) = explode(':',base64_decode($content));
	}

	$u = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
	$p = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

	if ($u === $user['user'] && tw_chkhash($p, $user['pass'])) return TRUE;
	else return FALSE;
}

/*
 * <TWCMS>
 * Prints a false 404 page
 * Intended to match the 404 page of Apache almost
 * byte for byte (including headers).
 *
 * Prevents dynamic files from being discovered
 */
function print404() {
	header('HTTP/1.1 404 Not Found');
	header('X-Powered-By:',TRUE);
	header('Set-Cookie',TRUE);
	?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL <?php print $_SERVER['REQUEST_URI']; ?> was not found on this server.</p>
<hr>
<address>Apache Server at <?php print $_SERVER['SERVER_NAME']; ?> Port 80</address>
</body></html>
	<?php
}

// EOF
