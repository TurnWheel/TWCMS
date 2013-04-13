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
	// Require U_SUPER permissions or throw error
	if (!user_restrict(U_SUPER)) return;
}

// If no user module, fallback to built-in HTTP auth
// Login credentials are set in global config file
else {
	// Require admin auth
	if (!check_auth('admin')) req_auth('Restricted Area');
}

$T['title'] = $T['header'] = 'View Error Data';

$content = '';

if (!$cfg['error']['savedb']) {
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

/*
 * Show full error details
 */
$eid = isset($H['id']) ? (int) $H['id'] : 0;
if ($eid !== 0) {
	$error = error_get($eid);
	$content = '';

	if ($error === FALSE) {
		$T['content'] .= '
		<div class="box error">
			<p>
				<strong>Error:</strong>
				Could not locate specified error.
			</p>
		</div>';
		return;
	}

	$T['title'] = $T['header'] = 'Error Data (#'.$eid.')';
	$content = '
	<a href="/admin/error/">&lt;&lt; Back To Error List</a>';

	$vals = $cfg['error']['vals'];
	$error_name = isset($vals[$error['error_num']]) ? $vals[$error['error_num']] : 'N/A';

	$content .= '
	<div id="e_error" class="errbox">
		<h2>Error Details</h2>
		<strong>Date:</strong> '.date('c', $error['date']).'<br />
		<strong>Error: </strong> '.$error['error_str'].'<br />
		<strong>Errno:</strong> '.$error['error_num'].' ('.$error_name.')<br />
		<strong>File:</strong> '.$error['error_file'].'<br />
		<strong>Line:</strong> '.$error['error_line'].'<br />
	</div><br /><br />

	<div id="e_trace" class="errbox">
		<h2>Full Callstack (Backtrace)</h2>
		'.$error['trace'].'
	</div>

	<div id="e_dump" class="errbox">
		<h2>Variable Dump</h2>
		'.$error['dump'].'
	</div>';

	$T['content'] = $content;
	return;
}
/* END Full Error Details */

$errors = error_getAll();

$content .= '
<table cellspacing="0" class="data" style="width:100%;">
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
			<span title="'.$error['error_str'].'">
				'.basename($error['error_file']).': '.$error['error_line'].';
				'.truncate($error['error_str'], 60).'
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
