<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 *
 * Simple thankyou page shown after registration
 */

// Make sure registration is enabled
// or return a 403 forbidden error
if (!$cfg['user']['regenable']) return p_showerror(403);

$T['title'] = $T['header'] = 'Thank You!';

$T['content'] = '
<div class="box success">
	<p>';

if ($cfg['user']['modreg']) {
	$T['content'] .= '
		<strong>Success!</strong> Your registration information has
		been received. Your account must be approved by a moderator
		before you can login to your account.
	</p>
	<p>
		You will receive an email notification when you have been approved.';
}
else {
	$T['content'] .= '
	<strong>Success!</strong> Your account has been created.
	You may now <a href="/login/">login to your new account</a>
	using the credentials you entered.';
}

$T['content'] .= '
	</p>
</div>';
