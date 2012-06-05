<?php
/*
 * TWCMS 1.1
 *
 * Set of security functions, such as HTTP auth,
 * errors and escape functions
 *
 * All functions here are fundamental to
 * the internals of TWCMS
 */

if (!defined('SECURITY')) exit;

/*
 * <TWCMS>
 * Escape headers for general use.
 */
function escape($v, $html = FALSE) {
	global $cfg;

	// Handle arrays recursively
	if (is_array($v)) {
		foreach ($v AS $k => $aval) {
			$v[$k] = escape($aval, $html);
		}

		return $v;
	}

	return !$html && $cfg['sql_enable'] ? mysql_real_escape_string($v)
			: htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

/*
 * <TWCMS>
 * Escape headers for use in includes,
 * and any file-based function
 */
function path_escape($v) {
	return preg_replace('/(\/\.\/)|[\/\\\]|(\.\.)/', '', $v);
}

/*
 * <TWCMS>
 * Shortcut for escaping HTML
 * This is kind of a legacy left-over, but still useful
 */
function html_escape($v) {
	return escape($v, TRUE);
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
 * Verify HTTP Auth
 * Input: array('user' => 'me', 'pass' => sha1('test'))
 * Or string representing name in cfg auth config
 */
function check_auth($login) {
	// If a string, get login from cfg['auth']
	if (is_string($login)) {
		$login = $GLOBALS['cfg']['auth'][$login];
	}

	// For PHP5 CGI In conjunction
	// with mod_rewrite: E=HTTP_AUTH:%{HTTP:Authorization}
	if (isset($_SERVER['REDIRECT_HTTP_AUTH'])
			&& !empty($_SERVER['REDIRECT_HTTP_AUTH'])) {
		list($type, $content) = explode(' ', $_SERVER['REDIRECT_HTTP_AUTH'], 2);
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
				explode(':', base64_decode($content));
	}

	$u = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
	$p = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

	if ($u === $login['user'] && tw_chkhash($p, $login['pass'])) return TRUE;
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
	header('X-Powered-By:', TRUE);
	header('Set-Cookie', TRUE);
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
