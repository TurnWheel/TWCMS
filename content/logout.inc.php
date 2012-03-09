<?php
// End session and logout
user_logout();

$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
$info = parse_url($ref);

// If hostname is set, and does not much current host
// then reset URL as / (home page)
if (isset($info['host']) && !empty($info['host'])
		&& $info['host'] !== DOMAIN) {
	$ref = '/';
}

header('Location: '.$ref);

// Create some page content in case redirect fails (or is slow)
$T['title'] = $T['header'] = 'Logout';
$T['content'] = '
<p>
	You have been logged out.
	Redirecting to <a href="'.$ref.'">'.
	($ref === '/' ? 'Home Page' : 'Previous Page').'</a>...
</p>';

// End of file