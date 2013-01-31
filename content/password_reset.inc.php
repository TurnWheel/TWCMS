<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 */

$T['title'] = $T['header'] = 'Password Reset';

// Force run of forgot_cron, which clears out old hashes
user_forgot_cron();

// Headers used in password recovery
$H['uid'] = isset($H['uid']) ? intval($H['uid']) : 0;
$H['hash'] = isset($H['hash']) ? escape($H['hash']) : '';

// Validate headers
if ($H['uid'] === 0 || $H['hash'] === '') {
	$T['content'] = '
	<div class="box error">
		<strong>Error!</strong> The page you have arrived at
		is invalid. If this link was sent to you in a password recovery
		email, please try using <a href="/password/">the recovery tool</a> again.
		Password recovery links expire after 24 hours.
	</div>';

	return FALSE; // Skip rest of file
}

// Validate ID/Hash
$rid = user_forgot_verify($H['uid'], $H['hash']);

// If no found, display error (most likely expired)
if ($rid === FALSE) {
	$T['content'] = '
	<div class="box error">
		<p>
			<strong>Error!</strong> The page you have arrived at
			is no longer valid. Most likely the 24 hour expiration period has passed.
			Please try using <a href="/password/">the recovery tool</a> again.
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
				<form method="post" action="/password/reset/uid:'.$H['uid'].'/hash:'.$H['hash'].'/">
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

$reset = user_forgot_reset($H['uid'], $rid);

if ($reset === FALSE) {
	$T['content'] = '
	<div class="box error">
		<p>
			<strong>Error!</strong> Password reset failed. This should not
			happen unless something went terribly wrong. Please
			<a href="/contact/">report this to the site administrator.</a>
		</p>
	</div>';

	return FALSE; // Skip rest of file
}

// Display success message
$T['content'] = '
	<div class="box success">
		<p>
			<strong>Success!</strong> You password has been reset.
			You should receive an email within the next 15 minutes
			containing your new password. If you do not receive this email,
			check your Spam folders.
		</p>
		<p>
			If you fail to receive the email, you can always attempt another
			<a href="/password/">Password Reset</a>.
		</p>
	</div>';

// EOF
