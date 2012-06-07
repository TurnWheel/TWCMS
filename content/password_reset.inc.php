<?php
/*
 * TWCMS <Module>
 *
 * Part of User Module
 */

$T['title'] = $T['header'] = 'Password Reset';

// Delete password recovery entries
// that are more than 24 hours old
sql_query('DELETE FROM user_pass WHERE date < '.(NOW-86400),
			'', __FILE__, __LINE__);

// Headers used in password recovery
$_GET['uid'] = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
$_GET['hash'] = isset($_GET['hash']) ? escape($_GET['hash']) : '';

// Validate headers
if ($_GET['uid'] === 0 || $_GET['hash'] === '') {
	$T['content'] = '
	<div class="box error">
		<strong>Error!</strong> The page you have arrived at
		is invalid. If this link was sent to you in a password recovery
		email, please try using <a href="/password">the recovery tool</a> again.
		Password recovery links expire after 24 hours.
	</div>';

	return FALSE; // Skip rest of file
}

// Validate ID/Hash
sql_query('SELECT recoverid FROM user_pass
			WHERE userid = "%d" AND hash = "%s"',
				array($_GET['uid'], $_GET['hash']));
$r = sql_fetch_array();

// If no found, display error (most likely expired)
if ($r === FALSE) {
	$T['content'] = '
	<div class="box error">
		<p>
			<strong>Error!</strong> The page you have arrived at
			is no longer valid. Most likely the 24 hour expiration period has passed.
			Please try using <a href="/password">the recovery tool</a> again.
		</p>
	</div>';

	return FALSE; // Skip rest of file
}

// Display "Are You Sure?" form
if (!isset($_POST['accept'])) {
	$T['content'] = '
	<div class="box">
		<p>
			You have reached the password reset area. Once you have chosen to
			reset your password, this action <strong>can not be un-done</strong>.
		</p>
		<p>
			Once submitted, you should receive an email containing
			the newly generated password.
		</p>
	</div><br />

	<fieldset>
		<legend>Password Reset</legend>
		<div>
			<p>Are you sure you wish to reset your account password?</p>
			<div style="width:200px;float:left;">
				<form method="post" action="/password/reset?uid='.$_GET['uid'].'&amp;hash='.$_GET['hash'].'">
					<button type="submit" name="accept">Yes, Reset Password</button>
				</form>
			</div>
			<div style="width:200px;float:left;">
				<form method="post" action="/">
					<button type="submit" name="cancel">No, Cancel</button>
				</form>
			</div>
			<div class="clear"></div><br />
		</div>
	</fieldset>';

	return FALSE; // Skip rest of file
}

// Get user email address
sql_query('SELECT email FROM user WHERE userid = "%d" LIMIT 1', $_GET['uid']);
$user = sql_fetch_array();

// Make sure patient exists (should never happen)
if ($user === FALSE) {
	$T['content'] = '
	<div class="box error">
		<p>
			<strong>Error!</strong> The account password you are trying to
			reset no longer exists. This is an error message you should never see.
			If you are seeing this, please
			<a href="/contact">report this to the site administrator.</a>
		</p>
	</div>';

	return FALSE; // Skip rest of file
}

// Generate New Password
$rid = (int) $r['recoverid']; // Recover ID

// Generate random password and salt
$salt = '';
$realpass = substr(tw_genhash(mt_rand()), 0, 10);
$hash = tw_genhash($realpass, TRUE, $salt);

// Update password in DB
sql_query('UPDATE user SET password = "%s", salt = "%s"
			WHERE userid = "%d" LIMIT 1',
				array($hash, $salt, $_GET['uid']));

// Remove temporary password recovery entry
sql_query('DELETE FROM user_pass WHERE recoverid = "'.$rid.'" LIMIT 1');

// Email variables
$email = $cfg['user_emails']['pass_reset'];
$email['to'] = $user['email'];
$map = array(
	'password' => $realpass
);

tw_sendmail($email, $map);

// Display success message
$T['content'] = '
	<div class="box success">
		<p>
			<strong>Success!</strong> You password has been reset.
			You should receive an email within the next 15 minutes
			containing your new password. If you do not receive this email,
			check your Spam folders. Otherwise, you may want to attempt
			<a href="/password">another password reset</a>.
		</p>
	</div>';

// EOF
