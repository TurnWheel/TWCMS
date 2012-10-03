<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 *
 * Allows user to edit profile and settings
 */

if (!ISUSER) return p_showerror(403);

$T['title'] = $T['header'] = 'Update User Profile';

// Parse user data into proper array
$data = array(
	'id' => (int) $U['userid'],
	'email' => html_escape($U['email']),
	'firstname' => html_escape($U['firstname']),
	'lastname' => html_escape($U['lastname']),
	'phone' => html_escape($U['phone']),
	'zip' => html_escape($U['zip']),
	'date' => (int) $U['date']
);

// Start buffer
ob_start();

// Stores error arrays
$error = array();

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
	$fields = array('currpass', 'newpass', 'newpass2');
	foreach ($fields AS $field) {
		$_POST[$field] = isset($_POST[$field]) ? escape($_POST[$field]) : '';
		if ($_POST[$field] === '') $error[$field] = TRUE;
	}

	// Verify current password
	if (!tw_chkhash($_POST['currpass'], $U['password'], $U['salt'])) {
		$error['currpass'] = 'Current password incorrect. Try again.';
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
					array($hash, $salt, $data['id']), __FILE__, __LINE__);

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

<form method="post" action="/user/profile/">
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

<form method="post" action="/user/profile/">
<fieldset>
	<legend>Reset Password</legend>
	<table cellspacing="0">
		<tr class="row0">
			<td><label for="currpass"<?php t_iserror($error, 'currpass'); ?>>Current Password</label></td>
			<td><input type="password" name="currpass" id="currpass" value="" /></td>
		</tr>
		<tr class="row1">
			<td><label for="newpass"<?php t_iserror($error, 'newpass'); ?>>New Password</label></td>
			<td><input type="password" name="newpass" id="newpass" value="" /></td>
		</tr>
		<tr class="row0">
			<td><label for="newpass2"<?php t_iserror($error, 'newpass2'); ?>>Re-Type Password</label></td>
			<td><input type="password" name="newpass2" id="newpass2" value="" /></td>
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
