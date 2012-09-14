<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 *
 * Admin Area to manage user account details
 */

// Verify permissions
// User module must be included for this area to load
if (!tw_isloaded('user') || !user_restrict(U_ADMIN)) return;

// Check if we should load detail page
if (isset($H['id']) && $H['id'] !== 0) {
	require CPATH.'admin_user.detail.inc.php';
	return; // Skip rest of file
}

$T['title'] = $T['header'] = 'User Management';

// Display table of users to manage
$html = '';

sql_query('SELECT * FROM user ORDER BY userid ASC', '', __FILE__, __LINE__);
$users = array();
while ($r = sql_fetch_array()) {
	$users[] = array(
		'id' => (int) $r['userid'],
		'firstname' => escape($r['firstname']),
		'lastname' => escape($r['lastname']),
		'email' => escape($r['email']),
		'date' => (int) $r['date'],
		'flags' => (int) $r['flags']
	);
}

$html .= '
<table cellspacing="0" class="data" style="width:100%;">
	<thead>
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>E-Mail</th>
			<th>Status</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>';

$i = 0;
foreach ($users AS $user) {
	$html .= '
	<tr class="table'.($i%2).'">
		<td class="center">
			<a href="/admin/user/'.$user['id'].'">'.$user['id'].'</a>
		</td>
		<td class="center">
			'.$user['firstname'].' '.$user['lastname'].'
		</td>
		<td class="center">'.$user['email'].'</td>
		<td class="center">
			'.(hasflag($user['flags'], U_LOGIN) ?
				'<strong class="green">Enabled</strong>' :
				'<strong class="red">Disabled</strong>').'
		</td>
		<td class="center">
			<a href="/admin/user/'.$user['id'].'">Manage</a>
		</td>
	</tr>';

	++$i;
}

$html .= '
	</tbody>
</table>';

$T['content'] = $html;

// EOF
