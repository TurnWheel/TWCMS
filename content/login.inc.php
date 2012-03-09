<?php
$T['title'] = $T['header'] = 'User Login';

if (ISUSER) {
	$T['content'] = '
	<div class="box notice">
		You are already logged in as <strong>'.$cfg['user']['firstname'].'</strong>.
		Please <a href="/logout">logout</a> if this is not your account.
	</div>';

	return FALSE; // Skip rest of file
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