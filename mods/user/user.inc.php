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

/*
 * Handles all the pre-conditions, login forms, cookie mangement,
 * and user verification
 */
function user_onLoad() {
	global $U;

	$isuser = FALSE; // Default to no user until verified
	$U = array(); // Empty setting for user prefs

	// Login user if POST was sent
	if (isset($_POST['login'])) {
		$email = isset($_POST['email']) ? escape($_POST['email']) : '';
		$pass = isset($_POST['password']) ? escape($_POST['password']) : '';

		if (user_login($U, $email, $pass)) $isuser = TRUE;
		// Set constant on failure so that content will be
		// replaced with proper error
		else define('LOGINFAILED', TRUE);

		// Set isuser flag as constant
		define('ISUSER', $isuser);

		if (!ISUSER) $U = array('flags' => U_GUEST);

		return;
	}

	// User login checking
	$estr = PREFIX.'_email';
	$pstr = PREFIX.'_hash';
	$email = isset($_COOKIE[$estr]) ? escape($_COOKIE[$estr]) : '';
	$pass = isset($_COOKIE[$pstr]) ? escape($_COOKIE[$pstr]) : '';

	// If both of these credentials cotain some information, process them
	// otherwise isuer stays FALSE
	if ($email !== '' && $pass !== '') {
		$isuser = user_verify($U, $email, $pass);

		// If cookies are set, and they did not validate
		// then logout user to remove cookies and session
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
				<a href="/register">register</a>.
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
				Please try entering your credentials again. Passwords are
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

			<label for="pass"'.($failed ? ' class="error"' : '').'>Password:</label>
			<input type="password" name="password" id="pass" /><br />

			<button type="submit" name="login"><strong>Login</strong></button><br />
			<a href="/password">(Forgot Password)</a>
		</div>
	</fieldset>
	</form>';

	return $content;
}

/*
 * Verify login credentials
 *
 * Salt flag adds salt to password before comparing
 */
function user_verify(&$user, $email, $pass, $salt = FALSE) {
	if ($email === '' || $pass === '') return FALSE;

	sql_query('SELECT * FROM user WHERE email = "%s" LIMIT 1',
				$email, __FILE__, __LINE__);
	$user = sql_fetch_array();

	// Validates email address
	if ($user === FALSE) return FALSE;

	// Verify the user has login permissions
	if (!hasflag(U_LOGIN, $user['flags'])) {
		define('LOGINDENIED', TRUE);
		return FALSE;
	}

	// Validates password (real or cookie hash)
	if ($salt) {
		if (!tw_chkhash($pass, $user['password'], $user['salt'])) {
			unset($pass);
			return FALSE;
		}
	}
	else {
		if ($user['password'] !== $pass) return FALSE;
		/* TODO: Add session validation through IP checking to prevent hijacks */
	}

	// Unset sensitive variables
	unset($pass);

	return TRUE;
}

/*
 * Verifies login credentials
 * and sets proper cookies if true
 *
 * TRUE: Login successful
 * FALSE: Bad user/pass
 */
function user_login(&$user, $email, $pass) {
	global $cfg;

	// Verify credentials
	if (user_verify($user, $email, $pass, TRUE)) {
		// Save cookies
		$time = NOW+$cfg['user_expire'];
		setcookie(PREFIX.'_email', $email, $time, BASEURL);
		setcookie(PREFIX.'_hash', $user['password'], $time, BASEURL);

		// Run login event
		tw_event('onUserLogin');

		return TRUE;
	}

	return FALSE;
}

/*
 * Logout user by resetting cookies and destroying current sessiona
 */
function user_logout() {
	// Delete cookies
	setcookie(PREFIX.'_email', '', NOW-3360, BASEURL);
	setcookie(PREFIX.'_hash', '', NOW-3360, BASEURL);

	// Destroy session and start a new one 
	session_destroy();
	session_start();

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
 */
function user_hasperm($perm) {
	global $U;
	return hasflag($perm, $U['flags']);
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