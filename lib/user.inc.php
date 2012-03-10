<?php
/*
 * TurnWheel CMS
 * Set of user function
 *
 * Optional in most cases
 */

if (!defined('SECURITY')) exit;

/*
 * Handles all the pre-conditions, login forms, cookie mangement,
 * and user verification
 *
 * Needs to be called onload when user.inc.php is first included
 */
function user_onload() {
	global $U;

	$isuser = FALSE; // Default to no user until verified
	$U = array(); // Empty setting for user prefs

	// Login user if POST was sent
	if (isset($_POST['login'])) {
		$email = isset($_POST['email']) ? escape($_POST['email']) : '';
		$pass = isset($_POST['password']) ? escape($_POST['password']) : '';

		// Login user if login is value
		if (user_login($U, $email, $pass)) $isuser = TRUE;

		// Set constant on failure so that content will be
		// replaced with proper error
		else define('LOGINFAILED', TRUE);

		// Set isuser flag as constant
		define('ISUSER', $isuser);

		return;
	}

	// User login checking
	$c_email = isset($_COOKIE[PREFIX.'_email']) ?
					escape($_COOKIE[PREFIX.'_email']) : '';
	$c_pass = isset($_COOKIE[PREFIX.'_hash']) ?
					escape($_COOKIE[PREFIX.'_hash']) : '';

	// If both of these credentials cotain some information, process them
	// otherwise isuer stays FALSE
	if ($c_email !== '' && $c_pass !== '') {
		$isuser = user_verify($U, $c_email, $c_pass);
	}

	// Set isuser flag as constant
	define('ISUSER', $isuser);

	// Set guest variables if not user
	if (!ISUSER) {
		$U['flags'] = U_GUEST;
		return;
	}

	// Set userid as a integer if available
	// usually comes out as string, integer is easier for comparisons
	if (isset($U['userid'])) {
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
			<p>
				<strong>Error!</strong> There was a problem with your login.
				Please try entering your credentials again. Passwords are
				case sensitive.
			</p>
		</div><br />';
	}

	// Generate HTML form
	$content .= '
	<form method="post" action="'.(REQUESTURL === '/login' ? '/' : REQUESTURL).'">
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

	sql_query('SELECT * FROM user WHERE email = "%s" LIMIT 1', $email);
	$user = sql_fetch_array();

	// Validates email address
	if ($user === FALSE) return FALSE;

	// Validates password (real or cookie hash)
	if ($salt) {
		if (!tw_chkhash($pass, $user['password'], $user['salt'])) return FALSE;
	}
	else {
		if ($user['password'] === $pass) return TRUE;
		/* TODO: Add session validation through IP checking to prevent hijacks */
	}

	// Unset sensitive variables
	unset($user['pass'], $user['salt']);

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

		return TRUE;
	}

	return FALSE;
}

/*
 * Logout user by resetting cookies and destroying current sessiona
 */
function user_logout() {
	setcookie(PREFIX.'_email', '', NOW-3360, BASEURL);
	setcookie(PREFIX.'_hash', '', NOW-3360, BASEURL);

	session_destroy();

	return TRUE;
}

/*
 * Register user
 * Creates a new user account with the provided params
 *
 * $data is just a hash table with row => value data
 * $pass is the raw password, to be encrypted
 *
 * Returns FALSE on failure (bool)
 * Returns USERID on success (int)
 */
function user_register($data, $pass) {
	// Create password
	$salt = '';
	$hash = tw_genhash($pass, TRUE, $salt);

	// Remove raw pass from memory
	unset($pass);

	// Set flags to U_DEFAULT if not specified
	$flags = isset($data['flags']) ? (int) $data['flags'] : U_DEFAULT;

	sql_query('INSERT INTO user ($keys) VALUES($vals)',
				array(
					'password' => $hash,
					'salt' => $salt,
					'date' => NOW,
					'flags' => $flags
				));

	$userid = sql_insert_id();
	return $userid;
}

/*
 * Permission Checks
 * Ex: user_hasperm(U_ADMIN)
 */
function user_hasperm($perm) {
	global $U;
	return check_flag($perm, $U['flags']);
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
	else tw_showerror(403);

	return FALSE;
}

// End of file