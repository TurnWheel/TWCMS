<?php
// Set header/title
$T['title'] = $T['header'] = 'Contact Us';
$T['content'] = '';

// Form Variables
$error = array();
$data = form_process('contact', $error);

// Only process if form was submitted
if (isset($_POST['submit'])) {
	// Check for failure and display error
	if (sizeof($error) !== 0) {
		$T['content'] = '
		<div class="box error">
			<p>
				<strong>Error!</strong> There was a problem
				with your submission.<br />

				Check the fields highlighted in red below.
			</p>
		</div>';
	}
	// Otherwise display success
	// if $cfg['forms']['redirect'] is set, this will never display
	else {
		$T['content'] = '
		<div class="box success">
			<p>
				<strong>Success!</strong> Your message has been sent.
				Thank you for contacting us. We will respond shortly.
			</p>
		</div>';
	}
}

// Start output buffer
ob_start();

// Display a welcome message
// as long as there are no form errors to display
if (sizeof($error) === 0) {
?>


	<p>
		If you would like to know more about our
		services and wish to contact us directly, <br />
		please call us or fill out the form below to send us an email.
	</p><br />

<?php
}

?>

<form method="post" action="/contact">
<fieldset>
	<legend>Contact Us</legend>
	<div>
		<table cellspacing="0">
			<tr>
				<td><label for="contact-name"<?php print isset($error['name']) ? ' class="error"' : ''; ?>>Name</label></td>
				<td><input type="text" name="name" id="contact-name" value="<?php print $data['name']; ?>" /></td>
			</tr>
			<tr>
				<td><label for="contact-email"<?php print isset($error['email']) ? ' class="error"' : ''; ?>>Email</label></td>
				<td><input type="text" name="email" id="contact-email" value="<?php print $data['email']; ?>" /></td>
			</tr>
			<tr>
				<td><label for="contact-message"<?php print isset($error['message']) ? ' class="error"' : ''; ?>>Message</label></td>
				<td>
					<textarea name="message" id="contact-message" cols="30" rows="10"><?php print stripslashes($data['message']); ?></textarea>
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
$T['content'] .= ob_get_contents();
ob_end_clean();

// End of file
