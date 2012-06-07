<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 *
 * Admin Area to manage user account details
 */

// Get user data based on ID
sql_query('SELECT * FROM user WHERE userid = "%d" LIMIT 1',
			(int) $_GET['id'], __FILE__, __LINE__);
$user = sql_fetch_array();

// Make sure user exists
if ($user === FALSE) {
	$T['title'] = $T['header'] = 'Error: User Not Found';
	$T['content'] = '
	<div class="box error">
		<p>
			<strong>Error!</strong>
			User could not be found. Please go back and try again.
		</p>
		<p><a href="/admin/user">&lt;&lt; User Management</a></p>
	</div>';

	return; // Skip rest of file
}

$T['title'] = $T['header'] = 'Edit User Profile';

$data = array(
	'id' => (int) $_GET['id'],
	'email' => html_escape($user['email']),
	'firstname' => html_escape($user['firstname']),
	'lastname' => html_escape($user['lastname']),
	'phone' => html_escape($user['phone']),
	'zip' => html_escape($user['zip']),
	'date' => (int) $user['date'],
	'flags' => (int) $user['flags']
);

// Start buffer
ob_start();

// Stores error arrays
$error = array();

// Check for change in status
if (isset($_POST['chstatus'])) {
	// Check for U_LOGIN
	// if true, remove U_LOGIN from flags
	// otherwise add U_LOGIN from flags
	if (check_flag(U_LOGIN, $data['flags'])) {
		// Remove U_LOGIN
		$data['flags'] = $data['flags'] & ~U_LOGIN;
	}
	else {
		// Add U_LOGIN
		$data['flags'] |= U_LOGIN;

		// Alert user of approval if user_modreg is enabled
		// and they have not previously been notified
		if ($cfg['user_modreg'] && !check_flag(U_NOTIFIED, $data['flags'])) {
			// Add U_NOTIFIED flag
			$data['flags'] |= U_NOTIFIED;

			$map = array(
				'date' => date($cfg['user_emails']['date'], NOW),
				'firstname' => $data['firstname'],
				'lastname' => $data['lastname'],
				'wwwurl' => WWWURL,
				'sslurl' => SSLURL
			);

			// Send out email
			$email = $cfg['user_emails']['approved'];
			$email['to'] = $data['email'];

			tw_sendmail($cfg['user_emails']['approved'], $map);

			$notified = TRUE;
		}
	}

	sql_query('UPDATE user SET flags = "%d" WHERE userid = "%d"',
				array($data['flags'], $data['id']), __FILE__, __LINE__);

	print '
	<div class="box success">
		<p>
			<strong>Success!</strong> The users status has been updated.
			'.(isset($notified) ? 'They have been notified of this change.' : '').'
		</p>
	</div>';
}

// Check for submission of profile update
if (isset($_POST['profile'])) {
	$fields = array('firstname', 'lastname', 'phone', 'zip');

	// Verify all POST data and check for empty fields
	foreach ($fields AS $field) {
		$_POST[$field] = isset($_POST[$field]) ? html_escape($_POST[$field]) : '';
		if ($_POST[$field] === '') $error[$field] = TRUE;
	}

	// If no errors, update user fields
	if (sizeof($error) === 0) {
		foreach ($fields AS $field) {
			$data[$field] = $_POST[$field];
		}

		sql_query('UPDATE user SET firstname = "%s", lastname = "%s",
					phone = "%s", zip = "%s" WHERE userid = "%d" LIMIT 1',
					array(
						$data['firstname'], $data['lastname'],
						$data['phone'], $data['zip'], $data['id']
					), __FILE__, __LINE__);

		print '
		<div class="box success">
			<p>
				<strong>Success!</strong> Your profile has been updated.
			</p>
		</div>';
	}
	else {
		$errmsg = '<strong>Error!</strong> Some fields were empty or invalid.
				Check the fields highlighted in red below.';
	}
}

// Check for password update
if (isset($_POST['pass'])) {
	$fields = array('newpass', 'newpass2');
	foreach ($fields AS $field) {
		$_POST[$field] = isset($_POST[$field]) ? escape($_POST[$field]) : '';
		if ($_POST[$field] === '') $error[$field] = TRUE;
	}

	// Verify new passwords are equal
	if ($_POST['newpass'] !== $_POST['newpass2']) {
		$error['newpass'] = 'Entered passwords do not match';
	}

	// If no errors, update password
	if (sizeof($error) === 0) {
		$salt = '';
		$hash = tw_genhash($_POST['newpass'], TRUE, $salt);

		sql_query('UPDATE user SET password = "%s", salt = "%s"
					WHERE userid = "%d"',
					array($hash, $salt, $data['id']),
					__FILE__, __LINE__);

		print '
		<div class="box success">
			<p>
				<strong>Success!</strong> Password has been updated
				for this user account
			</p>
		</div>';
	}
	else {
		$errmsg = '<strong>Error!</strong> Some fields were empty or invalid.
				Check the fields highlighted in red below.';
	}
}

if (sizeof($error) > 0) {
	print '
	<div class="box error">
		<p>'.(isset($errmsg) ? $errmsg : '').'</p>';

	// Print out errors with string
	foreach ($error AS $field => $reason) {
		if (!is_string($reason) || empty($reason)) continue;

		print '
		<p>- <strong>'.html_escape($reason).'</strong></p>';
	}

	print '
	</div>';
}
?>
<div>
	<p><a href="/admin/user">&laquo; Go Back To User Management</a></p>
</div>

<form method="post" action="/admin/user/<?php print $data['id']; ?>">
<fieldset>
	<legend>Account Status</legend>
	<p>
			<strong>Status:</strong>
	<?php
	if (check_flag(U_LOGIN, $data['flags'])) {
		print '
		<strong class="green">Active</strong></p>
		<button type="submit" name="chstatus">Disable Account</button><br />';
	}
	else {
		print '
		<strong class="red">Disabled</strong></p>';
		
		if ($cfg['user_modreg'] && !check_flag(U_NOTIFIED, $data['flags'])) {
			print '
			<p><strong>Important:</strong> This user will receive an
			email notification when this account is approved</p>';
		}

		print '
		<button type="submit" name="chstatus">
			'.($cfg['user_modreg'] ?
				(check_flag(U_NOTIFIED, $data['flags']) ? 'Enable Account' :
					'Approve Account') :
				'Enable Account').'
		</button><br />';
	}
	?>
</fieldset>
</form>

<form method="post" action="/admin/user/<?php print $data['id']; ?>">
<fieldset>
	<legend>Account Profile</legend>
	<table cellspacing="0">
		<tr class="row0">
			<td><strong>Date Registered</strong></td>
			<td><?php print date('m/d/Y H:i:s', $data['date']); ?>
		</tr>
		<tr class="row1">
			<td><strong>Email</strong></td>
			<td><?php print $data['email']; ?></td>
		</tr>
		<tr class="row0">
			<td><label for="firstname"<?php t_iserror($error, 'firstname'); ?>>First Name</label></td>
			<td><input type="text" name="firstname" id="firstname" value="<?php print $data['firstname']; ?>" /></td>
		</tr>
		<tr class="row1">
			<td><label for="lastname"<?php t_iserror($error, 'lastname'); ?>>Last Name</label></td>
			<td><input type="text" name="lastname" id="lastname" value="<?php print $data['lastname']; ?>" /></td>
		</tr>
		<tr class="row0">
			<td><label for="phone"<?php t_iserror($error, 'phone'); ?>>Phone #</label></td>
			<td><input type="text" name="phone" id="phone" value="<?php print $data['phone']; ?>" /></td>
		</tr>
		<tr class="row1">
			<td><label for="zip"<?php t_iserror($error, 'zip'); ?>>Zip Code</label></td>
			<td><input type="text" name="zip" id="zip" value="<?php print $data['zip']; ?>" /></td>
		</tr>
		<tr>
			<td colspan="2">
				<button type="submit" name="profile">Update Profile</button>
			</td>
		</tr>
	</table>
</fieldset>
</form>

<form method="post" action="/admin/user/<?php print $data['id']; ?>">
<fieldset>
	<legend>Reset Password</legend>
	<table cellspacing="0">
		<tr class="row1">
			<td><label for="newpass"<?php t_iserror($error, 'newpass'); ?>>New Password</label></td>
			<td><input type="text" name="newpass" id="newpass" value="" /></td>
		</tr>
		<tr class="row0">
			<td><label for="newpass2"<?php t_iserror($error, 'newpass2'); ?>>Re-Type Password</label></td>
			<td><input type="text" name="newpass2" id="newpass2" value="" /></td>
		</tr>
		<tr class="row1">
			<td colspan="2">
				<button type="submit" name="pass">Change Password</button>
			</td>
		</tr>
	</table>
</fieldset>
</form>

<?php
// Get contents from buffer and end
$T['content'] = ob_get_contents();
ob_end_clean();

// EOF
