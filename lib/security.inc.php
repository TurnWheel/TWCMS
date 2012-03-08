<?php
/*
 * TurnWheel CMS
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
function real_escape($v) {
	global $cfg;
	return $cfg['db_enable'] ? mysql_real_escape_string($v)
		: htmlspecialchars($v);
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
 * Loads any error page in CPATH/error.<num>.html
 */
function tw_showerror($num) {
	global $cfg, $_t;

	$num = path_escape($num);
	$file = CPATH.'error.'.$num.'.html';

	if (!isset($cfg['httpCodes'][$num])) return FALSE;
	if (!file_exists($file)) return FALSE;

	header('HTTP/1.1 '.$cfg['httpCodes'][$num]);

	// Split out header from body (first line is header)
	$_t['content'] = file_get_contents($file);
	$split = explode("\n", $_t['content'], 2);

	// Split header and content
	if (count($split) > 1) {
		$_t['header'] = $split[0];
		$_t['content'] = $split[1];
	}
	else $_t['header'] = '';

	// Strip out HTML from header (tends to sneak in)
	$_t['title'] = $_t['header'] = strip_tags($_t['header']);

	return TRUE;
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
 * byte for byte (including headers). To prevent
 * the revelation of buried files
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
