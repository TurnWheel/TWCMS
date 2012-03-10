<?php
/*
 * Admin area front-end for form data
 * allows exploration of all raw formdata collected over multiple forms
 */

// Require auth
if (!check_auth($cfg['admin'])) req_auth('Restricted');

$T['title'] = 'Admin: Form Data';
$T['content'] = '';

// Check if ID has been set
// If so, display entry details
if ($_GET['id'] !== 0) {
	$T['title'] .= ': Entry #'.$_GET['id'];
	$T['header'] = 'Viewing Entry #'.$_GET['id'];

	// Get entry data from DB
	sql_query('SELECT entryid,data,name,date FROM forms
					WHERE entryid = "%d" LIMIT 1', $_GET['id']);

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
		<a href="/formdata?form='.$info['name'].'">
			&lt;&lt; More Entries from "'.$info['name'].'"
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
		$key = htmlspecialchars($key);
		$value = htmlspecialchars($value);

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

// Get form name ($_GET['form'])
$fname = isset($_GET['form']) ? escape($_GET['form']) : '';

// No form name set, show possible form options
if ($fname === '') {
	print '
	<h3>Select Which Form Data to view</h3>
	<ul>';

	sql_query('SELECT name FROM forms GROUP BY name');

	if (sql_num_rows() === 0) {
		print '<li><em>No data available for viewing</em></li>';
	}
	else {
		while ($r = sql_fetch_array()) {
			print '
			<li><a href="/formdata?form='.$r['name'].'">'.$r['name'].'</a></li>';
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
<p><a href="/formdata">&lt;&lt; Select Different Form</a></p>';

sql_query('SELECT entryid AS id,data,name,date FROM forms
			WHERE name = "%s"
			ORDER BY date DESC', $fname);
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
			<a href="/formdata?id='.$r['id'].'">'.$r['id'].'</a>
		</td>
		<td class="center">
			'.date('F j, Y, g:i a',$r['date']).'
		</td>
		<td class="center">
			<a href="/formdata?id='.$r['id'].'">View Details</a>
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
