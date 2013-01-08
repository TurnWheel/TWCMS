<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 1.0
 * Author: Steven Bower
 * TurnWheel Designs (cc) 2012
 *
 * Complete User Module
 * Important config options in config.inc.php
 * This module comes with additional includes other than this library
 *
 * ---- Additional Files ----
 * content/user.inc.php
 * content/user_profile.inc.php
 * content/admin_user.inc.php
 * content/admin_user.detail.inc.php
 * content/password.inc.php
 * content/password_reset.inc.php
 * content/register.inc.php
 * content/register_thankyou.inc.php
 * content/login.inc.php
 * content/logout.inc.php
 * js/<pre>.admin_user.js
 * css/<pre>.admin_user.css
 * ----
 */

if (!defined('SECURITY')) exit;

/* Displays menu link in admin */
function user_adminMenu() {
	return array(
		'url' => '/admin/user/',
		'text' => 'User Management'
	);
}

/* Displays menu link in user profile */
function user_userMenu() {
	return array(
		'url' => '/user/profile/',
		'text' => 'Update Profile',
		'descrip' => 'Update your passphrase, account settings,
		or personal profile.'
	);
}

/*
 * Handles all the pre-conditions, login forms, cookie mangement,
 * and user verification
 */
function user_onLoad() {
	global $U, $cfg;

	$isuser = FALSE; // Default to no user until verified
	$U = array(); // Empty setting for user prefs

	// SQL Module is required
	if (!tw_isloaded('sql')) return FALSE;

	// Session config
	session_set_cookie_params($cfg['user_expire'], BASEURL, DOMAIN);

	// Login user if POST was sent
	if (isset($_POST['login'])) {
		$email = isset($_POST['email']) ? escape($_POST['email']) : '';
		$pass = isset($_POST['password']) ? escape($_POST['password']) : '';
		$remem = isset($_POST['remember']) ? TRUE : FALSE;

		if (user_login($U, $email, $pass, $remem)) $isuser = TRUE;
		// Set constant on failure so that content will be
		// replaced with proper error
		else define('LOGINFAILED', TRUE);

		// Set isuser flag as constant
		define('ISUSER', $isuser);

		if (!ISUSER) $U = array('flags' => U_GUEST);

		return;
	}

	// Verify session data
	$email = isset($_SESSION['email']) ? escape($_SESSION['email']) : '';
	$pass = isset($_SESSION['token']) ? escape($_SESSION['token']) : '';
	if ($email !== '' && $pass !== '') {
		$isuser = user_verify($U, $email, $pass);

		// If not valid, then force logout and destroy session
		if (!$isuser) user_logout();
	}

	// Set isuser flag as constant
	define('ISUSER', $isuser);

	// Set guest variables if not user
	if (!ISUSER) $U = array('flags' => U_GUEST);
	// Set userid as a integer if available
	// usually comes out as string, integer is better for comparisons
	elseif (isset($U['userid'])) {
		$U['userid'] = (int) $U['userid'];
	}
}

/*
 * Generates HTML login form
 *
 * This is the only HTML inside this library
 * allows for consolidation and consitency
 *
 * Error Flag: TRUE to display an error of no login, or improper permissions
 * to access restricted areas
 *
 * LOGINFAILED constant determines if the login failed (after use of form)
 */
function user_showlogin($error = TRUE) {
	$content = '';

	// Flag to determine if login has failed
	$failed = defined('LOGINFAILED');

	// Display access message
	if ($error) {
		$content .= '
		<div class="box error">
			<p>
				<strong>Sorry,</strong> but you must be logged into an
				authorized account to view this page.
				Please login using the form below, or
				<a href="/register/">register</a>.
			</p>
		</div><br />';
	}

	if ($failed) {
		$content .= '
		<div class="box error">
			<p>';

		if (defined('LOGINDENIED')) {
			$content .= '
				<strong>Error!</strong> Your account has not
				been activated. You are unable to login to your account
				at this time.';
		}
		else {
			$content .= '
				<strong>Error!</strong> There was a problem with your login.
				Please try entering your credentials again. Passphrases are
				case sensitive.';
		}

		$content .= '
			</p>
		</div><br />';
	}

	// Generate HTML form
	$content .= '
	<form method="post" action="'.REQUESTURL.'">
	<fieldset>
		<legend>User Login</legend>
		<div>
			<label for="email"'.($failed ? ' class="error"' : '').'>Email:</label>
			<input type="text" name="email" id="email" /><br />

			<label for="pass"'.($failed ? ' class="error"' : '').'>Passphrase:</label>
			<input type="password" name="password" id="pass" /><br />

			<input type="checkbox" name="remember" id="remember"
				value="yes" checked="checked" />
			<label for="remember">Stay Logged In?</label><br />

			<input type="hidden" name="referer" value="'.REFERER.'" />

			<button type="submit" name="login"><strong>Login</strong></button><br />
			<a href="/password/">(Forgot Passphrase)</a>
		</div>
	</fieldset>
	</form>';

	return $content;
}

/*
 * Verify login credentials
 *
 * $salt (bool): Adds salt to password before comparing
 */
function user_verify(&$U, $email, $pass, $salt = FALSE) {
	if ($email === '' || $pass === '') return FALSE;

	sql_query('SELECT * FROM user WHERE email = "%s" LIMIT 1',
				$email, __FILE__, __LINE__);
	$U = sql_fetch_array();

	// Essentially email address
	if ($U === FALSE) return FALSE;

	// Verify the user has login permissions
	if (!hasflag($U['flags'], U_LOGIN)) {
		define('LOGINDENIED', TRUE);
		return FALSE;
	}

	// Validates password (typed or session data)
	if ($salt) {
		if (!tw_chkhash($pass, $U['password'], $U['salt'])) {
			unset($pass);
			return FALSE;
		}
	}
	else {
		// Verifies both password hash and login token
		if (tw_token($U['password']) !== $pass) return FALSE;
	}

	// Unset sensitive variables
	unset($pass);

	return TRUE;
}

/*
 * Verifies login credentials and sets session data
 *
 * TRUE: Login successful
 * FALSE: Bad user/pass
 */
function user_login(&$U, $email, $pass, $remember = TRUE) {
	global $cfg;

	// Verify credentials
	if (user_verify($U, $email, $pass, TRUE)) {
		if (!$remember) {
			ini_set('session.cookie_lifetime', 0);
		}

		// Save session values
		$_SESSION['email'] = $email;
		$_SESSION['token'] = tw_token($U['password']);

		// Run login event
		tw_event('onUserLogin');

		return TRUE;
	}

	return FALSE;
}

/*
 * Logout user by resetting session and destroying current sessiona
 */
function user_logout() {
	$_SESSION = array();

	session_destroy();
	session_start();
	session_regenerate_id(TRUE);

	// Run logout event
	tw_event('onUserLogout');

	return TRUE;
}

/*
 * Register user
 * Creates a new user account with the provided params
 *
 * $data is just a hash table with row => value data
 * Required fields: email, password
 * flags will be set to U_DEFAULT unless specified otherwise
 *
 * Returns FALSE on failure (bool)
 * Returns USERID on success (int)
 */
function user_register($data) {
	global $cfg;

	// Verify data
	if (!isset($data['password']) || !isset($data['email'])) {
		return FALSE;
	}

	// Generate password hash
	$salt = '';
	$data['password'] = tw_genhash($data['password'], TRUE, $salt);

	// Set flags to U_DEFAULT if not specified
	$flags = isset($data['flags']) ? (int) $data['flags'] : U_DEFAULT;

	// Save user registration information to DB
	sql_query('INSERT INTO user ($keys) VALUES($vals)',
				array_merge($data, array(
					'salt' => $salt,
					'date' => NOW,
					'flags' => $flags
				)), __FILE__, __LINE__);

	$userid = sql_insert_id();

	// Remove password from $data
	// so that it does not end up in emails
	unset($data['password']);

	/* Email Notifications */
	$map = $data; // Use all fields as the starting of map
	$map['userid'] = $userid;

	// List all POST feilds in one map variable
	$map['fields'] = '';
	foreach ($data AS $name => $value) {
		$name = ucwords(str_replace('_',' ',$name));
		$map['fields'] .= $name.': '.$value."\n";
	}

	// Send email to moderator?
	// Either a registration notification,
	// or a mod email requesting activation
	$email = FALSE;
	if ($cfg['user_modreg']) $email = $cfg['user_emails']['modreg'];
	elseif ($cfg['user_regnotify']) $email = $cfg['user_emails']['regnotify'];

	if ($email !== FALSE) tw_sendmail($email, $map);

	// Should we send user a welcome message?
	if ($cfg['user_welcome']) {
		tw_sendmail($cfg['user_emails']['welcome'], $map);
	}

	// Register event
	tw_event('onUserRegister');

	return $userid;
}

/*
 * Permission Checks
 * Ex: user_hasperm(U_ADMIN)
 *
 * Returns hasflag(), except a input of 0 will return TRUE
 */
function user_hasperm($perm) {
	global $U;
	return $perm === 0 || hasflag($U['flags'], $perm);
}

/*
 * Restricts users from specific areas
 * unless they have specified permission flag
 *
 * If not logged in, it shows built in login form
 * Otherwise a 403 error is thrown (error.403.html)
 */
function user_restrict($perm) {
	global $T;

	// If they have perm, continue...
	if (user_hasperm($perm)) return TRUE;

	if (!ISUSER) {
		$T['title'] = $T['header'] = 'User Login';
		$T['content'] = user_showlogin();
	}
	else p_showerror(403);

	return FALSE;
}

// End of file