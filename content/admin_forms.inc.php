<?php
/*
 * TWCMS <Module>
 *
 * Part of Forms Module
 * Admin area front-end for form data
 * allows exploration of all raw formdata collected over multiple forms
 */

if (!tw_isloaded('forms')) return p_showerror(403);

if (tw_isloaded('user')) {
	// Require U_ADMIN permissions or throw error
	if (!user_restrict(U_ADMIN)) return;
}
else {
	// Require admin auth
	if (!check_auth('admin')) req_auth('Restricted Area');
}

$T['title'] = 'Admin: Form Data';
$T['content'] = '';

// Check if ID has been set
// If so, display entry details
if (isset($H['id']) && $H['id'] !== 0) {
	$T['title'] .= ': Entry #'.$H['id'];
	$T['header'] = 'Viewing Entry #'.$H['id'];

	// Get entry data from DB
	sql_query('SELECT entryid, data, name, date FROM forms
		WHERE entryid = "%d" LIMIT 1',
		$H['id'], __FILE__, __LINE__);

	$info = sql_fetch_array();

	// Display error if no data found
	if ($info === FALSE) {
		$T['content'] .= '
		<div class="box error">
			<p>
				<strong>Error!</strong> Could not find
				specified entry. Please go back and try again.
			</p>
		</div>';
		return FALSE;
	}

	$T['content'] .= '
	<p>
		<a href="/admin/forms?form='.$info['name'].'">
			&lt;&lt; More Entries from "'.$info['name'].'" form
		</a>
	</p>';

	// Get form data
	$data = unserialize($info['data']);

	// Start Table
	$T['content'] .= '
	<table cellspacing="0">
		<tr class="table1">
			<th scope="row">Date</th>
			<td>'.date('F j, Y, g:i a',$info['date']).'
		</tr>';

	// Print out all form data into table
	$k = 0;
	foreach ($data AS $key => $value) {
		// Escape form data just to be safe
		$key = html_escape($key);
		$value = html_escape($value);

		$T['content'] .= '
		<tr class="'.($k%2).'">
			<th scope="row">'.ucwords(str_replace('_',' ',$key)).'</th>
			<td>'.$value.'</td>
		</tr>';

		++$k;
	}

	$T['content'] .= '
	</table>';

	return; // Finish processing
}

$header = 'Viewing Form Data';

// Start output buffer
ob_start();

// Get form name ($H['form'])
$fname = isset($H['form']) ? escape($H['form']) : '';

// No form name set, show possible form options
if ($fname === '') {
	print '
	<h3>Select Which Form Data to view</h3>
	<ul>';

	sql_query('SELECT name FROM forms GROUP BY name', '', __FILE__, __LINE__);

	if (sql_num_rows() === 0) {
		print '<li><em>No data available for viewing</em></li>';
	}
	else {
		while ($r = sql_fetch_array()) {
			print '
			<li><a href="/admin/forms?form='.$r['name'].'">'
			.ucwords($r['name']).'</a></li>';
		}
	}

	print '
	</ul>';

	// Save buffer to content
	$T['content'] = ob_get_contents();
	ob_end_clean();

	// Finish processing
	return;
}

// At this point, $fname should be set
// show data for this for name
$header .= ': "'.$fname.'"';

print '
<p><a href="/admin/forms">&lt;&lt; Select Different Form</a></p>';

sql_query('SELECT entryid AS id,data,name,date FROM forms
			WHERE name = "%s"
			ORDER BY date DESC', $fname, __FILE__, __LINE__);
?>

<table cellspacing="0">
	<tr>
		<th scope="col">ID #</th>
		<th scope="col">Date</th>
		<th scope="col">Action</th>
	</tr>
<?php
// Format data into tables
$k = 0;
while ($r = sql_fetch_array()) {
	print '
	<tr class="table'.($k%2).'">
		<td class="center">
			<a href="/admin/forms/'.$r['id'].'">'.$r['id'].'</a>
		</td>
		<td class="center">
			'.date('F j, Y, g:i a',$r['date']).'
		</td>
		<td class="center">
			<a href="/admin/forms/'.$r['id'].'">View Details</a>
		</td>
	</tr>';

	++$k;
}
?>
</table>

<?php
// Save buffer to content
$T['content'] = ob_get_contents();
ob_end_clean();

// EOF
