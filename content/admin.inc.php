<?php
/*
 * Restricted area example
 */

// If user module is enabled, use user_restrict
// as this uses the built in user permission system
if ($cfg['user_enable']) {
	// Require U_ADMIN permissions or throw error
	if (!user_restrict(U_ADMIN)) return;
}

// If no user module, fallback to built-in HTTP auth
// Login credentials are set in config file
else {
	// Require admin auth
	if (!check_auth($cfg['admin_login'])) req_auth('Restriected Area');
}

$T['title'] = $T['header'] = 'Restricted Area';

print '
<p>
	This is a restricted area. You must have ADMIN permissions to view this page.
</p>';

// EOF