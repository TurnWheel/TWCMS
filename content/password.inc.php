<?php
/*
 * TWCMS <Module>
 *
 * Part of User Module
 */

$T['title'] = $T['header'] = 'Forgot Password';

// Check for user status
if (ISUSER) {
	$content = '
	<div class="box notice">
		<p>
			You are already logged in as a user. You may
			<a href="/user/settings">update your password on your settings page</a>
			or you may <a href="/logout">logout</a> and request a password reset.
		</p>
	</div>';

	return FALSE; // Skip rest of file
}

$error = array(); // Holds form errors
$useremail = isset($_POST['email']) ? escape($_POST['email']) : '';

// Check for form submission
if (isset($_POST['submit'])) {
	// Validate email format
	if (!valid_email($useremail)) $error['email'] = TRUE;

	// Verify Email Exists in Database
	sql_query('SELECT userid FROM user WHERE email = "%s" LIMIT 1', $useremail);
	$u = sql_fetch_array();

	// Error if not found
	if ($u === FALSE) $error['email'] = TRUE;

	// If no errors, process request
	if (sizeof($error) === 0) {
		$uid = (int) $u['userid']; // Patient ID
		$hash = tw_genhash($uid.NOW); // Create a unique hash

		// Setup temporary DB
		sql_query('INSERT INTO user_pass ($keys) VALUES($vals)',
					array('userid' => $uid,
					'hash' => $hash,
					'date' => NOW));

		// Email variables
		$email = $cfg['user_emails']['pass_forgot'];
		$email['to'] = $useremail;
		$map = array(
			'reseturl' => WWWURL.'password/reset?uid='.$uid.'&hash='.urlencode($hash)
		);

		// Send out email
		tw_sendmail($email, $map);

		// Show successful message
		$T['content'] = '
		<div class="box success">
			<p>
				An email has been dispatched to the entered email address.
				You should receive this email with in the next 30 minutes.
				The email will contain further instructions on how to reset
				your password.
			</p>
		</div>';

		return; // Skip rest of file
	}
}

// Start output buffer
ob_start();
?>

<p>
	Enter your email address below to initiate the password reset process.
	You should receive an email at that address within 10 minutes.
	The email will contain further instructions on how to reset your password.
</p>

<?php
if (sizeof($error) > 0) {
	print '
	<div class="box error">
		<p>
			<strong>Sorry!</strong> The email you have entered is invalid.
			Please try again.
		</p>
	</div>';
}
?>

<form method="post" action="/password">
<fieldset>
	<legend>Reset Password Form</legend>
	<div>
		<label for="email"<?php print isset($error['email']) ? ' class="error"' : ''; ?>>
			<strong>Registered E-Mail:</strong>
		</label>
		<input type="text" name="email" id="email" value="<?php print html_escape($useremail); ?>" />
	</div>
</fieldset>

<button type="submit" name="submit">Send Instructions</button>
</form>

<?php
// Save and clean output buffer
$T['content'] = ob_get_contents();
ob_end_clean();

// EOF