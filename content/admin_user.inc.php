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

sql_query('SELECT * FROM user
	ORDER BY userid ASC',
	'', __FILE__, __LINE__);
$users = array();
while ($r = sql_fetch_array()) {
	$users[(int) $r['userid']] = array(
		'firstname' => html_escape($r['firstname']),
		'lastname' => html_escape($r['lastname']),
		'email' => html_escape($r['email']),
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

foreach ($users AS $id => $user) {
	$html .= '
	<tr class="center">
		<td>
			<a href="/admin/user/'.$id.'/">'.$id.'</a>
		</td>
		<td>
			'.$user['firstname'].' '.$user['lastname'].'
		</td>
		<td>'.$user['email'].'</td>
		<td>
			'.(hasflag($user['flags'], U_LOGIN) ?
				'<strong class="green">Enabled</strong>' :
				'<strong class="red">Disabled</strong>').'
		</td>
		<td>
			<a href="/admin/user/'.$id.'/">Manage</a>
		</td>
	</tr>';
}

$html .= '
	</tbody>
</table>';

$T['content'] = $html;

// EOF
