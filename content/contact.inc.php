<?php
// Set header/title
$T['title'] = $T['header'] = 'Contact Us';

// Start output buffer
ob_start();

// Form Variables
$error = array(); // Array of form errors
$required = array('name','email','message'); // Array of required fields

// Headers
$_POST['name'] = isset($_POST['name']) ? escape($_POST['name']) : '';
$_POST['email'] = isset($_POST['email']) ? escape($_POST['email']) : '';
$_POST['message'] = isset($_POST['message']) ? escape($_POST['message']) : '';

if (isset($_POST['submit'])) {
	// Clean message from return chars
	$_POST['message'] = str_replace('\r\n',"\n",$_POST['message']);

	// Verify required fields
	foreach ($required AS $field) {
		if ($_POST[$field] === '' || $_POST[$field] === 0) {
			$error[$field] = TRUE;
		}
	}

	// Verify email
	if (!valid_email($_POST['email'])) $error['email'] = TRUE;

	// Check for errros
	if (sizeof($error) > 0) {
		// Print error message
		print '
		<div class="box error">
			<p>
				<strong>Error!</strong> There was a problem
				with your submission.<br />

				Check the fields highlighted in red below.
			</p>
		</div>
		';
	}
	else {
		ob_end_clean(); // End buffer

		// Generate map of variables for email
		$map = array(
			'name' => $_POST['name'],
			'email' => $_POST['email'],
			'message' => stripslashes('\r\n',"\n",$_POST['message']),
			'date' => date($cfg['email_date'],NOW)
		);

		// Send email to admin
		$email = $cfg['contact_content'];
		mail(implode(',',$cfg['contact_admin']),map_replace($map,$email['subject']),
				map_replace($map,$email['body']),$cfg['contact_headers']);

		// Redirect
		header('Location: /contact/thankyou');

		return TRUE; // Skip rest of file
	}
}

if (sizeof($error) === 0) {
?>


	<p>
		If you would like to know more about our
		services and wish to contact us directly, <br />
		please call us or fill out the form below to send us an email.
	</p><br />

<?php
}

// Escape all post values
foreach ($_POST AS $key => $value) {
	$_POST[$key] = htmlspecialchars($_POST[$key]);
}
?>


<form method="post" action="/contact">
<fieldset>
	<legend>Contact Us</legend>
	<div>
		<table cellspacing="0">
			<tr>
				<td><label for="contact-name"<?php print isset($error['name']) ? ' class="error"' : ''; ?>>Name</label></td>
				<td><input type="text" name="name" id="contact-name" value="<?php print $_POST['name']; ?>" /></td>
			</tr>
			<tr>
				<td><label for="contact-email"<?php print isset($error['email']) ? ' class="error"' : ''; ?>>Email</label></td>
				<td><input type="text" name="email" id="contact-email" value="<?php print $_POST['email']; ?>" /></td>
			</tr>
			<tr>
				<td><label for="contact-message"<?php print isset($error['message']) ? ' class="error"' : ''; ?>>Message</label></td>
				<td>
					<textarea name="message" id="contact-message" cols="30" rows="10"><?php print stripslashes($_POST['message']); ?></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<button type="submit" name="submit">Send Message</button>
				</td>
			</tr>
		</table>
	</div>

</fieldset>
</form>

<?php
// Store output buffer and flush
$T['content'] = ob_get_contents();
ob_end_clean();

// End of file
