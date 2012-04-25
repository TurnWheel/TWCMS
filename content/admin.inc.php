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
// Login credentials are set in config file
else {
	// Require admin auth
	if (!check_auth($cfg['admin'])) req_auth('Restricted Area');
}

$T['title'] = $T['header'] = 'Restricted Area';

$T['content'] = '
<p>
	This is a restricted area. You must have ADMIN permissions to view this page.
</p>';

// If form module is loaded, display form link
if (isset($cfg['mods_loaded']['forms'])) {
	$T['content'] .= '
	<ul>';

	if (tw_isloaded('user')) {
		$T['content'] .= '
			<li><a href="/admin/user">User Management</a></li>';
	}

	if (tw_isloaded('forms')) {
		$T['content'] .= '
		<li><a href="/admin/forms">View Form Data</a></li>';
	}

	$T['content'] .= '
	</ul>';
}

// EOF
