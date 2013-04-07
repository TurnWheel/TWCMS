<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 0.6
 * Author: Steven Bower
 * BPWebDesign (cc) 2012
 *
 * Image upload wrapper
 * Makes managing of image uploads simpler and consistant
 * across modules.
 */

/*
 * Generates HTML for image element for use inside forms
 * Built in support for multiple images
 *
 * Name/path/url are only required if you wish to show existing images
 * Leave blank if there are none
 */
function upload_gethtml($name = '', $path = '', $url = '', $field = 'imgs', $max = 5) {
	global $cfg;

	$types = implode('|', $cfg['upload']['types']);
	$size = $cfg['upload']['maxsize'];

	$html = '<input type="hidden" name="MAX_FILE_SIZE" value="'.$size.'" />'."\n".
		'<input type="file" class="multi" '.
		'name="'.$field.'[]" accept="'.$types.'" '.
		'multiple="multiple" maxlength="'.$max.'" />';

	// Check if we need to display existing images
	if ($name !== '' && $path !== '' && $url !== '') {
		$current = upload_get($name, $path, $url, FALSE);

		// If no images, nothing left to do
		if ($current === FALSE) return $html;

		foreach ($current AS $key => $imgs) {
			$thumb = $imgs['thumb'];
			$html .= '
			<div class="upload-current-image">
				<img src="'.$thumb.'" title="Image '.$key.'" alt="Image '.$key.'" /><br />

				<input type="checkbox" name="defimg" id="upload-default-'.$key.
				'" value="'.($key === 'default' ? 0 : $key).'"'.
				($key === 'default' ? ' checked="checked"' : '').' />
				<label for="upload-default-'.$key.'">Default Image?</label><br />

				<input type="checkbox" name="delimgs[]" value="'.$key.'" id="upload-delete-'.$key.'" />
				<label for="upload-delete-'.$key.'" class="red">Delete Image?</label><br />
			</div>';
		}
	}

	return $html;
}

/*
 * Gets all images for specified path/ID
 *
 * $retdef: Return default images if none found?
 */
function upload_get($name, $path, $url, $retdef = TRUE) {

	// Verify inputs
	if ($name === '' || $path === '' || $url === '') {
		return FALSE;
	}

	// Three sizes
	$thumb = $name.'/*.thumb.*';
	$large = $name.'/*.large.*';
	$orig = $name.'/*.orig.*';

	$images = array();
	$find = glob($path.$thumb);

	// If fails to find any thumbnails, return default values
	if (!$find) {
		if (!$retdef) return FALSE;

		return array('default' => array(
			'thumb' => $url.'default.thumb.jpg',
			'large' => $url.'default.large.jpg',
			'orig' => $url.'default.orig.jpg'
		));
	}

	// Load all thumbnails and create base array of images
	foreach ($find AS $filepath) {
		$pinfo = pathinfo($filepath);
		$fname = $pinfo['basename'];
		$ext = $pinfo['extension'];

		$num = str_replace('.thumb.'.$ext, '', $fname);

		$images[$num]['thumb'] = $url.$name.'/'.$fname;
	}

	// Populate base array with large and original images, too
	// This simplifies the verification process
	foreach ($images AS $num => $val) {
		$currpath = $path.$name.'/';
		$pinfo = pathinfo($val['thumb']);
		$ext = $pinfo['extension'];

		$fname = $num.'.large.'.$ext;
		if (is_file($currpath.$fname)) {
			$images[$num]['large'] = $url.$name.'/'.$fname;
		}
		else $image[$num]['large'] = $url.'default.large.jpg';

		$fname = $num.'.orig.'.$ext;
		if (is_file($currpath.$fname)) {
			$images[$num]['orig'] = $url.$name.'/'.$fname;
		}
		else $images[$num]['orig'] = FALSE;
	}

	// Handles edge case:
	// If only one set of images is found, this set
	// should automatically be "default"
	if (count($images) === 1) {
		$default = current($images);
		$images = array('default' => $default);
	}

	return $images;
}

/*
 * Deletes specified images (all sizes)
 */
function upload_delete($name, $path, $key = 'default') {
	// Verify inputs
	if ($name === '' || $path === '' || $key === '') {
		return FALSE;
	}

	return array_map('unlink', glob($path.$name.'/'.$key.'.*.*'));
}

/*
 * Process and save images from a form
 * Generates all sizes and organizes into $path using $name as identifier
 */
function upload_process($files, $name, $path, $default = 0, $delete = array()) {
	global $cfg;

	// Verify inputs
	if ($name === '' || $path === '') {
		return FALSE;
	}

	$size_th = $cfg['upload']['size_thumb'];
	$size_lg = $cfg['upload']['size_large'];

	// Tracks which files should be saved,
	// and which are successfully saved
	$save = $status = array();

	// Delete any specified files before proceeding
	foreach ($delete AS $num => $key) {
		upload_delete($name, $path, $key);
	}

	foreach ($files['error'] AS $key => $error) {
		if ($error === UPLOAD_ERR_OK) {

			// Verify Extension with mime type
			$ext = str_replace('image/', '', $files['type'][$key]);

			// If ext is not in allowed list, skip this entry
			if (array_search($ext, $cfg['upload']['types']) === FALSE) {
				continue;
			}

			// Verify file size
			$size = $files['size'][$key];

			if ($size <= $cfg['upload']['maxsize']) {
				$save[] = array(
					'name' => $files['tmp_name'][$key],
					'ext' => $ext === 'jpeg' ? 'jpg' : $ext
				);
			}
		}
	}

	// If nothing saved, just set default and exit
	if (sizeof($save) === 0) {
		return upload_default($name, $path, $default);
	}

	// Create directory for these images
	$dir = $path.$name.'/';
	if (!is_dir($dir)) {
		mkdir($dir);
	}

	// Rename files and resize
	foreach ($save AS $key => $info) {
		$tmp = $info['name'];
		$ext = $info['ext'];

		$base = $dir.$key;
		$fname = $base.'.orig.'.$ext;

		// Prevents overrides, and forces increment
		while (is_file($fname)) {
			$key += 1;
			$fname = $dir.$key.'.orig.'.$ext;
		}

		if (move_uploaded_file($tmp, $fname)) {
			// Resize to small and large versions
			$status[$key] = upload_resize($fname, $base.'.thumb.'.$ext, $size_th);
			$status[$key] = upload_resize($fname, $base.'.large.'.$ext, $size_lg);
		}
		else {
			$status[$key] = FALSE;
		}
	}

	// Set default image
	upload_default($name, $path, $default);

	return $status;
}


/*
 * Sets default image
 */
function upload_default($name, $path, $key) {
	// Verify input
	if ($name === '' || $path === '' || $key === '') {
		return FALSE;
	}

	$dir = $path.$name.'/';

	$default = glob($dir.'default.thumb.*');
	$first = glob($dir.$key.'.thumb.*');

	// If there is no default, and the default files exist
	// then rename those files to 'default' explicitly
	if ($default == array() && $first !== array()) {
		$path = pathinfo($first[0]);
		$ext = $path['extension'];

		rename($dir.$key.'.thumb.'.$ext, $dir.'default.thumb.'.$ext);
		rename($dir.$key.'.large.'.$ext, $dir.'default.large.'.$ext);
		rename($dir.$key.'.orig.'.$ext, $dir.'default.orig.'.$ext);

		return TRUE;
	}

	return FALSE;
}

/*
 * Resizes uploaded images to specified dimensions proportionally
 *
 * $orig: Original file path
 * $to: Path to save resized image to
 * $size: Becomes the length of the largest side (px)
 * $quality: JPEG Compression
 */
function upload_resize($orig, $to, $size, $quality = 75) {
	// Verify inputs
	if ($orig === '' || $to === '' || (int) $size === 0) {
		return FALSE;
	}

	// Get extension from "to" path
	$path = pathinfo($to);
	$ext = $path['extension'];

	if ($ext === 'jpg') $ext = 'jpeg';

	$width = $height = (int) $size;
	list($width_orig, $height_orig) = getimagesize($orig);

	// If it doesn't need to be resized, copy original file
	if ($width_orig < $width && $height_orig < $height) {
		if (!copy($orig, $to)) return FALSE;
	}
	else {
		// Calculate proportional width/height
		if ($width && ($width_orig < $height_orig)) {
			$width = ($height/$height_orig)*$width_orig;
		}
		else $height = ($width/$width_orig)*$height_orig;

		$image_p = imagecreatetruecolor($width, $height);

		if (function_exists('imagecreatefrom'.$ext)) {
			$image = call_user_func('imagecreatefrom'.$ext, $orig);
		}
		else $image = FALSE;

		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width,
			$height, $width_orig, $height_orig);

		if ($ext === 'jpeg') {
			imagejpeg($image_p, $to, $quality);
		}
		elseif (function_exists('image'.$ext)) {
			call_user_func('image'.$ext, $image_p, $to);
		}
	}

	// Set permissions on new file
	chmod($to, 0777);

	return TRUE;
}

// EOF
