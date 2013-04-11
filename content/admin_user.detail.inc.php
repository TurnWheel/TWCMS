<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 *
 * Admin Area to manage user account details
 */

// Get user data based on ID
$user = user_get($H['id']);

// Make sure user exists
if ($user === FALSE) {
	$T['title'] = $T['header'] = 'Error: User Not Found';
	$T['content'] = '
	<div class="box error">
		<p>
			<strong>Error!</strong>
			User could not be found. Please go back and try again.
		</p>
		<p><a href="/admin/user/">&lt;&lt; User Management</a></p>
	</div>';

	return; // Skip rest of file
}

$T['title'] = $T['header'] = 'Edit User Profile';

// Start buffer
ob_start();

// Stores error arrays
$error = array();

// Watch for change in status
if (isset($_POST['chstatus'])) {
	$status = user_changeStatus($user);

	// Status change successful?
	if ($status !== FALSE) {
		$user['flags'] = $status['flags'];
		$user['active'] = $status['active'];

		print '
		<div class="box success">
			<p>
				<strong>Success!</strong> The users status has been updated.
				'.(isset($status['notified']) ?
				'They have been notified of this change.' : '').'
			</p>
		</div>';
	}
}

// Watch for change in permissions
if (isset($_POST['chperms'])) {
	$flags = isset($_POST['flags']) ? $_POST['flags'] : array();
	$perms = user_updatePerms($user, $flags);
	print_r($flags);
	var_dump($perms);


	if ($perms !== FALSE) {
		$user['flags'] = $perms;

		print '
		<div class="box success">
			<p>
				<strong>Success!</strong> The permissions on this account
				have been updated.
			</p>
		</div>';
	}
}

// Watch for submission of profile changes
if (isset($_POST['profile'])) {
	$fields = array('firstname', 'lastname', 'phone', 'zip');

	// Verify all POST data and check for empty fields
	foreach ($fields AS $field) {
		$user[$field] = isset($_POST[$field]) ? html_escape($_POST[$field]) : '';

		if ($user[$field] === '') {
			$error[$field] = TRUE;
		}
	}

	// If no errors, update user fields
	if (sizeof($error) === 0) {
		$update = user_profile($user);
		if (!$update) {
			$error['firstname'] = TRUE;
		}
	}

	if (sizeof($error) === 0) {
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

// Watch for password change
if (isset($_POST['pass'])) {
	$fields = array('newpass', 'newpass2');

	foreach ($fields AS $field) {
		$_POST[$field] = isset($_POST[$field]) ? escape($_POST[$field]) : '';
		if ($_POST[$field] === '') {
			$error[$field] = TRUE;
		}
	}

	// Verify new passwords are equal
	if ($_POST['newpass'] !== $_POST['newpass2']) {
		$error['newpass'] = 'Entered passwords do not match';
	}

	// If no errors, update password
	if (sizeof($error) === 0) {
		$pass = user_passwd($_POST['newpass'], $user['id']);

		if (!$pass) {
			$error['newpass'] = $error['newpass2'] = TRUE;
		}
	}

	if (sizeof($error) === 0) {
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
	<p><a href="/admin/user/">&laquo; Go Back To User Management</a></p>
</div>

<form method="post" action="/admin/user/<?php print $user['id']; ?>/">
<fieldset>
	<legend>Account Status</legend>
	<p>
			<strong>Status:</strong>
	<?php
	if ($user['active']) {
		print '
		<strong class="green">Active</strong></p>
		<button type="submit" name="chstatus">Disable Account</button><br />';
	}
	else {
		print '
		<strong class="red">Disabled</strong></p>';

		if ($cfg['user']['modreg'] && !hasflag($user['flags'], U_NOTIFIED)) {
			print '
			<p><strong>Important:</strong> This user will receive an
			email notification when this account is approved</p>';
		}

		print '
		<button type="submit" name="chstatus">
			'.($cfg['user']['modreg'] ?
				(hasflag($user['flags'], U_NOTIFIED) ? 'Enable Account' :
					'Approve Account') :
				'Enable Account').'
		</button><br />';
	}
	?>
</fieldset>
</form>

<form method="post" action="/admin/user/<?php print $user['id']; ?>/">
<fieldset>
	<legend>Permissions</legend>
	<strong>Available Access Levels</strong>
	<ul style="list-style:none;">
	<?php
	$i = 0;
	foreach ($cfg['user']['flags'] AS $flag => $name) {
		// Skip U_LOGIN has this is uniquely handeled by Account Status
		if ($flag === U_LOGIN) continue;

		$check  = hasflag($user['flags'], $flag);

		print '
		<li>
			<input type="checkbox" name="flags[]" value="'.$flag
			.'" id="flag_'.$i.'"'.($check ? ' checked="checked"' : '').' />
			<label for="flag_'.$i.'">'.$name.'</label>
		</li>';

		++$i;
	}
	?>
	</ul>
	<button type="submit" name="chperms">Update Permissions</button>
</fieldset>
</form>

<form method="post" action="/admin/user/<?php print $user['id']; ?>/">
<fieldset>
	<legend>Account Profile</legend>
	<table cellspacing="0">
		<tr class="row0">
			<td><strong>Date Registered</strong></td>
			<td><?php print date('m/d/Y H:i:s', $user['date']); ?>
		</tr>
		<tr class="row1">
			<td><strong>Email</strong></td>
			<td><?php print $user['email']; ?></td>
		</tr>
		<tr class="row0">
			<td><label for="firstname"<?php t_iserror($error, 'firstname'); ?>>First Name</label></td>
			<td><input type="text" name="firstname" id="firstname" value="<?php print $user['firstname']; ?>" /></td>
		</tr>
		<tr class="row1">
			<td><label for="lastname"<?php t_iserror($error, 'lastname'); ?>>Last Name</label></td>
			<td><input type="text" name="lastname" id="lastname" value="<?php print $user['lastname']; ?>" /></td>
		</tr>
		<tr class="row0">
			<td><label for="phone"<?php t_iserror($error, 'phone'); ?>>Phone #</label></td>
			<td><input type="text" name="phone" id="phone" value="<?php print $user['phone']; ?>" /></td>
		</tr>
		<tr class="row1">
			<td><label for="zip"<?php t_iserror($error, 'zip'); ?>>Zip Code</label></td>
			<td><input type="text" name="zip" id="zip" value="<?php print $user['zip']; ?>" /></td>
		</tr>
		<tr>
			<td colspan="2">
				<button type="submit" name="profile">Update Profile</button>
			</td>
		</tr>
	</table>
</fieldset>
</form>

<form method="post" action="/admin/user/<?php print $user['id']; ?>/">
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
