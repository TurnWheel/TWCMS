<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 */

if (!ISUSER) {
	return p_showerror(403);
}

$T['title'] = $T['header'] = 'Manage Your Account';

$T['content'] = '
<div class="box">
	<p>
		Use these tools to manage and update your account.
		If you have any questions about your membership, please
		<a href="/contact/">contact us</a>.
	</p>
</div>

<h3>Account Options</h3>
<ul>';

$links = tw_event('userMenu');
foreach ($links AS $mod => $item) {
	if (!is_array($item)) continue;

	// Supports restricting based on specific user permission
	if (isset($item['perms'])) {
		if (!user_hasperm($item['perms'])) continue;
	}

	$descrip = isset($item['descrip']) ? ': '.$item['descrip'] : '';

	$T['content'] .= '
	<li><a href="'.$item['url'].'">'.$item['text'].'</a>'.$descrip.'</li>';
}

$T['content'] .= '
</ul>';

// EOF
