<?php
/*
 * Default Admin Area
 * Also used as a sample of restricted area
 */

// If user module is enabled, use user_restrict
// as this uses the built in user permission system
if (tw_isloaded('user')) {
	// Require U_ADMIN permissions or throw error
	if (!user_restrict(U_ADMIN)) return;
}

// If no user module, fallback to built-in HTTP auth
// Login credentials are set in global config file
else {
	// Require admin auth
	if (!check_auth('admin')) req_auth('Restricted Area');
}

$T['title'] = $T['header'] = 'Restricted Area';

$T['content'] = '
<p>
	This is a restricted area. You must have ADMIN permissions to view this page.
</p>';

$T['content'] .= '
<ul>';

$links = tw_event('adminMenu');
foreach ($links AS $mod => $item) {
	if (!is_array($item)) continue;

	$T['content'] .= '
	<li><a href="'.$item['url'].'">'.$item['text'].'</a></li>';
}

$T['content'] .= '
</ul>';

// EOF
