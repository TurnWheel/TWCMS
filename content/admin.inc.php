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
<div class="box notice">
	<p>
		<strong>NOTICE</strong><br />
		This is a restricted area. All information accessible
		through this tool is for administrative use only.<br />
		Please use carefully.
	</p>
</div>';

$T['content'] .= '
<ul style="list-style:none;">';

$links = tw_event('adminMenu');
foreach ($links AS $mod => $item) {
	if (!is_array($item)) continue;

	// Supports restricting based on specific user permission
	if (tw_isloaded('user') && isset($item['perms'])) {
		if (!user_hasperm($item['perms'])) continue;
	}

	$descrip = isset($item['descrip']) ? $item['descrip'] : '';

	$T['content'] .= '
	<li class="box">
		<a href="'.$item['url'].'" class="icon admin-'.$mod.'">'.$item['text'].'</a><br />
		<p>'.$descrip.'</p>
	</li>';
}

$T['content'] .= '
</ul>
<div class="clear"></div>';

// EOF
