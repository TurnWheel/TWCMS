<?php
/*
 * TWCMS <Module>
 *
 * Admin area for file upload and management
 */

// Require FILES_U_ADMIN permissions or throw error
if (!user_restrict(FILES_U_ADMIN)) return;

$T['title'] = $T['header'] = 'Manage Resource Files';

$html = '';

// If upload detected
if (isset($_POST['send'])) {
	$file = isset($_FILES['upload']) ? $_FILES['upload'] : array();
	$cat = isset($_POST['cat']) ? html_escape($_POST['cat']) : '';

	$save = files_process($file, $cat);

	if ($save) {
		$html .= '
		<div class="box success">
			<p>
				<strong>Success!</strong><br />
				Your files have been saved!
			</p>
		</div>';
	}
	else {
		$html .= '
		<div class="box error">
			<p>
				<strong>Error!</strong>
				Some files did not process completely.
				'.(print_r($error, TRUE)).'
			</p>
		</div>';
	}
}

// If delete action detected
if (isset($_POST['delete'])) {
	$files = isset($_POST['files']) ? $_POST['files'] : array();
	$cat = isset($_POST['cat']) ? html_escape($_POST['cat']) : '';

	$delete = FALSE;
	foreach ($files AS $key => $file) {
		if (files_rm($cat, $file)) {
			$delete = TRUE;
		}
	}

	if ($delete) {
		$html .= '
		<div class="box success">
			<p>
				<strong>Selected file(s) were deleted</strong>
			</p>
		</div>';
	}
}

$types = implode('|', $cfg['files']['types']);

$html .= '
<form action="/admin/files/" method="post" enctype="multipart/form-data">
<fieldset>
	<legend>Upload File</legend>
	<div>
		<strong>Max Upload Size:</strong> '.bytes2num($cfg['files']['maxsize']).'<br />

		<label for="f-file">Select up to 3 files:</label>
		<input type="file" id="f-file" class="multi" name="upload[]"'.
		' multiple="multiple" accept="'.$types.'" maxlength="3" /><br />

		<label>Category:</label>
		<select name="cat" id="f-cat">';

		foreach ($cfg['files']['cats'] AS $catid => $cat) {
			$html .= '
			<option value="'.$catid.'">'.$cat['title'].'</option>';
		}

		$html .= '
		</select><br /><br />

		<input type="hidden" name="MAX_FILE_SIZE" value="'.$cfg['files']['maxsize'].'" />
		<button type="submit" name="send">Upload</button>

		<br /><br />
	</div>
</fieldset>
</form>';

foreach ($cfg['files']['cats'] AS $catid => $cat) {
	$html .= '
	<h3>'.$cat['title'].'</h3>';

	$files = files_list($catid);

	// If empty, display message
	if ($files === array()) {
		$html .= '
		<p>No files for this category. Upload files above.</p>';
	}

	$html .= '
	<form method="post" action="/admin/files/">
	<ul>';

	foreach ($files AS $k => $file) {
		$html .= '
		<li>
			<input type="checkbox" name="files[]" value="'.$file['name'].'" />
			<a href="'.$file['url'].'" rel="external">'.$file['name'].'</a> &mdash;
			'.bytes2num($file['size']).'
		</li>';
	}

	$html .= '
	</ul>
	<input type="hidden" name="cat" value="'.$catid.'" />
	<button type="submit" name="delete" class="confirm">Delete Selected</button>
	</form>
	<br /><br />';
}

$T['content'] = $html;
// EOF
