<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 */

$T['title'] = $T['header'] = 'User Login';

if (ISUSER) {
	$ref = isset($_POST['referer']) ? html_escape($_POST['referer']) : '/';
	$info = parse_url($ref);

	// If hostname is set, and does not much current host
	// then reset URL as / (home page)
	if (isset($info['host']) && !empty($info['host']) && $info['host'] !== DOMAIN) {
		$ref = '/';
	}

	// Exclude login page
	if ($ref === '/login' || $ref === '/login/') {
		$ref = '/';
	}

	header('Location: '.$ref);
	return; // Skip rest of file
}

$T['content'] = '
<div class="box">
	<p>
		If you are not currently a member,
		please <a href="/register/">register</a>.
		If you are having trouble with your password, use the
		<a href="/password/">Reset Password</a> tool.
	</p>
</div><br />';

$T['content'] .= user_showlogin(FALSE);

// End of file