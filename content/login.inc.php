<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 */

$T['title'] = $T['header'] = 'User Login';

if (ISUSER) {
	header('Location: /');
	return; // Skip rest of file
}

$T['content'] = '
<div class="box">
	<p>
		If you are not currently a member,
		please <a href="/register">register</a>.
		If you are having trouble with your password, use the
		<a href="/password">Reset Password</a> tool.
	</p>
</div><br />';

$T['content'] .= user_showlogin(FALSE);

// End of file