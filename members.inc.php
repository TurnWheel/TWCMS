<?php
$T['header'] = $T['title'] = 'Members';

$files = files_list('members');

// Returns FALSE if permission denied
if (!$files) {
	p_showerror(403);
	return FALSE;
}

$html = '
<div class="box notice">
	<strong>NOTICE</strong>
	<p>
		The following resources are made available to approved members only.
		Please do not share or re-distribute these files.
	</p>
</div>';

if ($files === array()) {
	$html .= '
	<p>No files currently available.</p>';
}

$html .= '
<ul>';

foreach ($files AS $k => $file) {
	$html .= '
	<li>
		<a href="'.$file['url'].'">'.$file['name'].'</a> &mdash;
		'.bytes2num($file['size']).'
	</li>';
}

$html .= '
</ul>';

$T['content'] = $html;

// EOF
