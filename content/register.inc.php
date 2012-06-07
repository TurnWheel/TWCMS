<?php
/*
 * TWCMS User Module
 *
 * Registration form
 */

// Make sure registration is enabled
// or return a 403 forbidden error
if (!$cfg['user_regenable']) return p_showerror(403);

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
		sql_query('SELECT userid FROM user WHERE email = "%s" LIMIT 1',
						$data['email'], __FILE__, __LINE__);
		if (sql_fetch_array() !== FALSE) {
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
			header('Location: /register/thankyou');
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

<form method="post" action="/register">
<fieldset id="register">
	<legend>Account Information</legend>
	<div>
		<table cellspacing="0">
			<tr class="row0">
				<td><label for="firstname"<?php t_iserror($error, 'firstname'); ?>>First Name</label></td>
				<td><input type="text" name="firstname" id="firstname" value="<?php print $data['firstname']; ?>" /></td>
			</tr>
			<tr class="row1">
				<td><label for="lastname"<?php t_iserror($error, 'lastname'); ?>>Last Name</label></td>
				<td><input type="text" name="lastname" id="lastname" value="<?php print $data['lastname']; ?>" /></td>
			</tr>
			<tr class="row0">
				<td>
					<label for="email"<?php t_iserror($error, 'email'); ?>>E-Mail</label><br />
					<small>(This will be used for login)</small>
				</td>
				<td><input type="text" name="email" id="email" value="<?php print $data['email']; ?>" /></td>
			</tr>
			<tr class="row1">
				<td><label for="phone"<?php t_iserror($error, 'phone'); ?>>Phone #</label></td>
				<td><input type="text" name="phone" id="phone" value="<?php print $data['phone']; ?>" /></td>
			</tr>
			<tr class="row0">
				<td><label for="zip"<?php t_iserror($error, 'zip'); ?>>Zip Code</label></td>
				<td><input type="text" name="zip" id="zip" value="<?php print $data['zip']; ?>" /></td>
			</tr>
			<tr class="row1">
				<td><label for="password"<?php t_iserror($error, 'password'); ?>>Password</label></td>
				<td><input type="password" name="password" id="password" value="<?php print $data['password']; ?>" /></td>
			</tr>
			<tr class="row0">
				<td><label for="password2"<?php t_iserror($error, 'password2'); ?>>Verify Password</label></td>
				<td><input type="password" name="password2" id="password2" value="<?php print $data['password2']; ?>" /></td>
			</tr>
		</table>
	</div>
</fieldset>

<div>
	<input type="checkbox" name="accept" id="accept" value="true"<?php print $data['accept'] !== '' ? ' checked="checked"' : '';?> />
	<label for="accept"<?php t_iserror($error, 'accept'); ?>>
		I have read and accept your <a href="/terms" rel="external">Terms of Use</a>
	</label>
</div><br />

<button type="submit" name="register">Register Account</button>
</form>

<?php
$T['content'] = ob_get_contents();
ob_end_clean();

// EOF
