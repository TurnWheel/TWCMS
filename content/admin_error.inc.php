<?php
/*
 * TWCMS <Module>
 *
 * Part of TWCMS Error Module
 *
 * Simply views all errors in the DB
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

$T['title'] = $T['header'] = 'View Error Data';

$content = '';

if (!$cfg['error_savedb']) {
	$content .= '
	<div class="box notice">
		<p>
			<strong>Notice:</strong>
			Currently errors are <strong>not</strong> being saved.
			If you wish to view errors, you must first enable
			the sql module, load error.sql, and enable
			<em>error_savedb</em> config option
		</p>
		<p>
			However, any error data previously saved will stil be shown below.
		</p>
	</div>';
}

sql_query('SELECT eid, error, date, flags FROM error');
$errors = array();
while ($r = sql_fetch_array()) {
	$errors[(int) $r['eid']] = array(
		'error' => htmlentities($r['error']),
		'date' => (int) $r['date'],
		'flags' => (int) $r['flags']
	);
}

$content .= '
<table cellspacing="0" class="data">
	<tr>
		<th scope="col">#</th>
		<th scope="col">Error</th>
		<th scope="col">Date</th>
	</tr>';

$i = 0;
foreach ($errors AS $id => $error) {
	$content .= '
	<tr class="table'.($i%2).'">
		<td><a href="/admin/error/'.$id.'/">#'.$id.'</a></td>
		<td>
			<span title="'.$error['error'].'">
				'.truncate($error['error'], 100).'
			</span>
		</td>
		<td>'.date('c', $error['date']).'</td>
	</tr>';
	++$i;
}

$content .= '
</table>';

$T['content'] = $content;

// EOF
