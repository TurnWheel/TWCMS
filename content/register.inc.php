<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 *
 * Simple Registration Form
 */

// Make sure registration is enabled
// or return a 403 forbidden error
if (!$cfg['user']['regenable']) {
	return p_showerror(403);
}

$T['title'] = $T['header'] = 'Register Account';

// Array of all form fields
$fields = array('firstname','lastname','email','password',
	'password2','phone','zip','accept');

// Array of required fields
$required = $fields; // (All Fields Required)
$error = array(); // Errors generated
$data = array();

foreach ($fields AS $field) {
	$data[$field] = isset($_POST[$field]) ? escape($_POST[$field]) : '';
}

// If registration data received
if (isset($_POST['register'])) {
	// Check Required Fields
	foreach ($required AS $field) {
		if (!isset($data[$field]) || empty($data[$field])) {
			$error[$field] = TRUE;
		}
	}

	/* Check Special Feilds */
	// Validate Email
	if ($data['email'] !== '' && !valid_email($data['email'])) {
		$error['email'] = 'Invalid Email Address';
	}

	// Compare passwords
	if ($data['password'] !== $data['password2']) {
		$error['password'] = 'Passwords do not match';
		$error['password2'] = TRUE;
	}

	// Check for unique email (if no email errors already)
	if (!isset($error['email'])) {
		if (user_exists($data['email']) !== FALSE) {
			$error['email'] = 'Email Address Already In Use';
		}
	}

	// If no errors, save and send emails, then redirect
	if (sizeof($error) === 0) {
		// Remove fields we do not want to save
		unset($data['password2'], $data['accept']);

		// Register user data. Returns FALSE on failure
		if (user_register($data) !== FALSE) {
			// Redirects to thankyou page on success
			header('Location: /register/thankyou/');
			exit;
		}
		else {
			$error['register'] = 'Failed to register; unknown error.
				Verify you filled out all forms properly.';
		}
	}
}

// Escape all form data for use in HTML
foreach ($data AS $key => $val) {
	$data[$key] = html_escape($val);
}

// Start output buffer
ob_start();

// Display errors if any
if (sizeof($error) > 0) {
	$errmsg = array();
	foreach ($error AS $key => $err) {
		if (!is_string($err)) continue;

		$errmsg[] = '<strong>'.ucwords(str_replace('_',' ',$key))
			.': </strong> '.$err;
	}

	print '
	<div class="box error">
		<p>
			<strong>Error!</strong>
			Some fields are missing or incomplete. Please check the
			fields highlighted in red below.
		</p>';

	if (sizeof($errmsg) > 0) {
		print '
		<p>'.implode('<br />',$errmsg).'</p>';
	}

	print '
	</div>';
}
?>

<form method="post" action="/register/">
<fieldset id="register">
	<legend>Account Information</legend>
	<table cellspacing="0">
		<tr class="row0">
			<th scope="row">
				<label for="reg_fname"<?php t_iserror($error, 'firstname'); ?>>First Name</label>
			</th>
			<td>
				<input type="text" name="firstname" id="reg_fname" value="<?php print $data['firstname']; ?>" />
			</td>
		</tr>
		<tr class="row1">
			<th scope="row">
				<label for="reg_lname"<?php t_iserror($error, 'lastname'); ?>>Last Name</label>
			</th>
			<td>
				<input type="text" name="lastname" id="reg_lname" value="<?php print $data['lastname']; ?>" />
			</td>
		</tr>
		<tr class="row0">
			<th scope="row">
				<label for="reg_phone"<?php t_iserror($error, 'phone'); ?>>Phone #</label>
			</th>
			<td>
				<input type="text" name="phone" id="reg_phone" value="<?php print $data['phone']; ?>" />
			</td>
		</tr>
		<tr class="row1">
			<th scope="row">
				<label for="reg_zip"<?php t_iserror($error, 'zip'); ?>>Zip Code</label>
			</th>
			<td>
				<input type="text" name="zip" id="reg_zip" value="<?php print $data['zip']; ?>" />
			</td>
		</tr>
		<tr class="row0">
			<th scope="row">
				<label for="reg_email"<?php t_iserror($error, 'email'); ?>>E-Mail</label><br />
				<small>(This will be used for login)</small>
			</th>
			<td>
				<input type="text" name="email" id="reg_email" value="<?php print $data['email']; ?>" />
			</td>
		</tr>
		<tr class="row1">
			<th scope="row">
				<label for="reg_password"<?php t_iserror($error, 'password'); ?>>Password</label>
			</th>
			<td>
				<input type="password" name="password" id="reg_password" value="<?php print $data['password']; ?>" />
			</td>
		</tr>
		<tr class="row0">
			<th scope="row">
				<label for="reg_password2"<?php t_iserror($error, 'password2'); ?>>Verify Password</label>
			</th>
			<td>
				<input type="password" name="password2" id="reg_password2" value="<?php print $data['password2']; ?>" />
			</td>
		</tr>
	</table>
</fieldset>

<div>
	<input type="checkbox" name="accept" id="accept" value="true"<?php print $data['accept'] !== '' ? ' checked="checked"' : '';?> />
	<label for="accept"<?php t_iserror($error, 'accept'); ?>>
		I have read and accept your <a href="/terms/" rel="external">Terms of Use</a>
	</label>
</div><br />

<button type="submit" name="register">Register Account</button>
</form>

<?php
$T['content'] = ob_get_contents();
ob_end_clean();

// EOF
