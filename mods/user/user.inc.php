<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 1.2
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
 * js/cms.admin_user.js
 * css/cms.admin_user.css
 * ----
 */

if (!defined('SECURITY')) exit;

/***
 * Event Functions
 ***/

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
	session_set_cookie_params($cfg['user']['expire'], BASEURL, DOMAIN);

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
 * Cron Job; clean out forgot password table
 */
function user_onCron() {
	return user_forgot_cron();
}

/***
 * Template Functions
 ***/

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

/***
 * Core Functions
 ****/

/*
 * Verify login credentials
 *
 * $salt (bool): Adds salt to password before comparing
 */
function user_verify(&$U, $email, $pass, $salt = FALSE) {
	// Verify input
	if ($email === '' || $pass === '') {
		return FALSE;
	}

	sql_query('SELECT * FROM user WHERE email = "%s" LIMIT 1',
		$email, __FILE__, __LINE__);
	$U = sql_array();

	// User not found
	if ($U === FALSE) {
		return FALSE;
	}

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
		if (tw_token($U['password']) !== $pass) {
			return FALSE;
		}
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
 * $notify: Optionally turn off all external notifications (emails)
 * used only for "silent" registration
 *
 * Returns FALSE on failure (bool)
 * Returns USERID on success (int)
 */
function user_register($data, $notify = TRUE) {
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
	if ($cfg['user']['modreg']) {
		$email = $cfg['user']['emails']['modreg'];
	}
	elseif ($cfg['user']['regnotify']) {
		$email = $cfg['user']['emails']['regnotify'];
	}

	if ($email !== FALSE && $notify) {
		tw_sendmail($email, $map);
	}

	// Should we send user a welcome message?
	if ($cfg['user']['welcome'] && $notify) {
		tw_sendmail($cfg['user']['emails']['welcome'], $map);
	}

	// Register event
	tw_event('onUserRegister');

	return $userid;
}

/***
 * Utility Functions
 ***/

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

/*
 * Does this user exists?
 * Returns userid if TRUE, otherwise returns FALSE
 */
function user_exists($email) {

	sql_query('SELECT userid FROM user WHERE email = "%s" LIMIT 1',
		$email, __FILE__, __LINE__);
	$u = sql_array();

	if (!$u) return FALSE;

	return (int) $u['userid'];
}

/***
 * Interfance Functions
 ***/

/*
 * Get all relevant user information
 * Password is not retreived
 *
 * Admin privledges required
 */
function user_get($uid) {
	global $U;

	$uid = (int) $uid;

	if ($uid === 0) return FALSE;

	// Verify Permissions
	if ($uid !== $U['userid'] && !user_hasperm(U_ADMIN)) {
		return FALSE;
	}

	sql_query('SELECT userid, firstname, lastname,
			email, phone, zip, date, flags
		FROM user WHERE userid = "%d"',
		$uid, __FILE__, __LINE__);

	$u = sql_array();

	if (!$u) return FALSE;

	$flags = (int) $u['flags'];

	return array(
		'id' => $uid,
		'userid' => $uid,
		'firstname' => html_escape($u['firstname']),
		'lastname' => html_escape($u['lastname']),
		'email' => $u['email'],
		'phone' => html_escape($u['phone']),
		'zip' => html_escape($u['zip']),
		'date' => (int) $u['date'],
		'flags' => $flags,
		'active' => hasflag($flags, U_LOGIN),
		'status' => user_getStatus($flags)
	);
}

/*
 * Verify raw password input matches account password
 */
function user_chkpasswd($pass) {
	global $U;

	return tw_chkhash($pass, $U['password'], $U['salt']);
}

/*
 * Changes user passwd
 *
 * $pass: New password in plain text
 * $uid: If admin, specify User ID, otherwise it uses current user
 */
function user_passwd($pass, $uid = 0) {
	global $U;

	// Verify userid is set
	$uid = (int) $uid;
	if ($uid === 0 && !isset($U['userid'])) {
		return FALSE;
	}

	// Which userid to change
	$uid = $uid === 0 ? $U['userid'] : (int) $uid;

	$salt = '';
	$hash = tw_genhash($pass, TRUE, $salt);

	sql_query('UPDATE user SET password = "%s", salt = "%s"
		WHERE userid = "%d" LIMIT 1',
		array($hash, $salt, $uid), __FILE__, __LINE__);

	return TRUE;
}

/*
 * Changes user profile
 *
 * $data: Array of fields. Does not verify, front-end must do that.
 * Expects: firstname, lastname, phone, zip
 *
 * $uid: If admin, specifiy User ID, otherwise it uses current user
 */
function user_profile($data, $uid = 0) {
	global $U;

	// Which userid to change
	$uid = (int) $uid === 0 ? $U['userid'] : (int) $uid;

	// Validate permissions
	if ($uid !== $U['userid'] && !user_hasperm(U_ADMIN)) {
		return FALSE;
	}

	sql_query('UPDATE user SET firstname = "%s", lastname = "%s",
		phone = "%s", zip = "%s" WHERE userid = "%d" LIMIT 1',
		array(
			$data['firstname'], $data['lastname'],
			$data['phone'], $data['zip'], $uid
		), __FILE__, __LINE__);

	return TRUE;
}

/*
 * Forgot password?
 * Step 1: Send email with link to step 2
 */
function user_forgot($email) {
	global $cfg;

	// Validate email format
	if (!valid_email($email)) {
		return FALSE;
	}

	// Verify Email Exists in Database
	$uid = user_exists($email);

	// Error if not found
	if (!$uid) {
		return FALSE;
	}

	// Create unique hash and save to DB
	$hash = tw_genhash($uid.NOW);

	sql_query('INSERT INTO user_pass ($keys) VALUES ($vals)',
		array(
			'userid' => $uid,
			'hash' => $hash,
			'date' => NOW
		), __FILE__, __LINE__);

	// Email variables
	$etemp = $cfg['user']['emails']['pass_forgot'];
	$etemp['to'] = $email;
	$map = array(
		'reseturl' => WWWURL.'password/reset/uid:'.$uid
			.'/hash:'.urlencode($hash).'/'
	);

	// Send out email
	tw_sendmail($etemp, $map);

	return TRUE;
}

/*
 * Forgot password
 * Step 2: Verify valid uid & hash input
 *
 * FALSE on failure
 * (int) recoverid on success
 */
function user_forgot_verify($uid, $hash) {

	sql_query('SELECT recoverid FROM user_pass
		WHERE userid = "%d" AND hash = "%s" LIMIT 1',
		array($uid, $hash), __FILE__, __LINE__);

	$r = sql_array();

	if ($r === FALSE) return FALSE;

	return (int) $r['recoverid'];
}

/*
 * Forgot password
 * Step 3: Complete Reset and send email
 */
function user_forgot_reset($uid, $rid) {
	global $cfg;

	// Get user email address
	sql_query('SELECT email FROM user WHERE userid = "%d" LIMIT 1',
		$uid, __FILE__, __LINE__);
	$user = sql_array();

	if ($user === FALSE) {
		return FALSE;
	}

	// Generate random password, and save
	$realpass = substr(tw_genhash(mt_rand()), 0, 10);

	if (!user_passwd($realpass, $uid)) {
		return FALSE;
	}

	// Remove temporary password recovery entry
	sql_query('DELETE FROM user_pass WHERE recoverid = "%d" LIMIT 1',
		$rid, __FILE__, __LINE__);

	// Email variables
	$email = $cfg['user']['emails']['pass_reset'];
	$email['to'] = $user['email'];
	$map = array(
		'passphrase' => $realpass
	);

	tw_sendmail($email, $map);

	return TRUE;
}

/*
 * Delete password recovery entries
 * that are more than 24 hours old
 */
function user_forgot_cron() {
	sql_query('DELETE FROM user_pass WHERE date < '.(NOW-86400),
		'', __FILE__, __LINE__);

	return TRUE;
}

/***
 * Admin Functions
 ***/

/*
 * Get all users in a single call
 *
 * Available to admin only
 */
function user_getAll($opts = FALSE) {
	// TODO: Allow search options
	$opts = array();

	if (!user_hasperm(U_ADMIN)) return FALSE;

	sql_query('SELECT * FROM user ORDER BY userid ASC',
		'', __FILE__, __LINE__);

	$users = array();
	while ($r = sql_array()) {
		$uid = (int) $r['userid'];
		$flags = (int) $r['flags'];

		$users[$uid] = array(
			'firstname' => html_escape($r['firstname']),
			'lastname' => html_escape($r['lastname']),
			'email' => html_escape($r['email']),
			'date' => (int) $r['date'],
			'flags' => $flags,
			'active' => hasflag($flags, U_LOGIN),
			'status' => user_getStatus($flags)
		);
	}

	return $users;
}

/*
 * User status, based on flags
 *
 * TODO: Generate smart status based on permissions
 */
function user_getStatus($perms) {
	return FALSE;
}

/*
 * Returns an array of all enabled permissions in the given flag
 *
 * $text: Returns text name of permissions
 */
function user_getPerms($flags, $text = FALSE) {
	global $cfg;

	if (!isset($cfg['user']['flags'])) return FALSE;

	$perms = array();
	foreach ($cfg['user']['flags'] AS $f => $txt) {
		if (hasflag($flags, $f)) {
			$perms[] = $text ? $txt : $f;
		}
	}

	return $perms;
}

/*
 * This function toggles the U_LOGIN status of the specified user
 *
 * $user: All user information as provided by user_get()
 *
 * Note: If this is their first time being "approved", the user
 * will receieve an auto email notification.
 *
 * Admin permissions required
 */
function user_changeStatus($user) {
	global $cfg;

	// Verify permissions
	if (!user_hasperm(U_ADMIN)) return FALSE;

	// Check for U_LOGIN
	// if true, remove U_LOGIN from flags
	// otherwise add U_LOGIN from flags
	if (hasflag($user['flags'], U_LOGIN)) {
		// Remove U_LOGIN
		$user['flags'] = rmflag($user['flags'], U_LOGIN);
	}
	else {
		// Add U_LOGIN
		$user['flags'] = addflag($user['flags'], U_LOGIN);

		// Alert user of approval if user_modreg is enabled
		// and they have not previously been notified
		if ($cfg['user']['modreg'] && !hasflag($user['flags'], U_NOTIFIED)) {
			// Add U_NOTIFIED flag
			$user['flags'] = addflag($user['flags'], U_NOTIFIED);

			$map = array(
				'date' => date($cfg['user']['emails']['date'], NOW),
				'firstname' => $user['firstname'],
				'lastname' => $user['lastname'],
				'wwwurl' => WWWURL,
				'sslurl' => SSLURL
			);

			// Send out email
			$email = $cfg['user']['emails']['approved'];
			$email['to'] = $user['email'];

			tw_sendmail($cfg['user']['emails']['approved'], $map);

			$notified = TRUE;
		}
	}

	$q = sql_query('UPDATE user SET flags = "%d" WHERE userid = "%d"',
			array($user['flags'], $user['id']), __FILE__, __LINE__);

	if (!$q) return FALSE;

	$status = array(
		'flags' => $user['flags'],
		'active' => hasflag($user['flags'], U_LOGIN)
	);

	if (isset($notified)) {
		$status['notified'] = TRUE;
	}

	return $status;
}

// End of file