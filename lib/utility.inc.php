<?php
/*
 * TWCMS 1.0
 *
 * Incredibly disorganized set of utility functions.
 * They are rather random, so easy to just throw them in here.
 *
 * If the func is denoted with <TWCMS>
 * this means it is vital to the CMS internals
 * Otherwise, it is just a useful function
 */

if (!defined('SECURITY')) exit;

/*
 * <TWCMS>
 * Check Bit Flags
 * Usage: check_flag(F_FLAG, $flags)
 */
function check_flag($flag, $val) {
	if (($flag&$val) === 0) return FALSE;
	else return TRUE;
}

/*
 * Check list of flags for greatest results
 * $text = array of (flag) => (text descrip)
 * $compare = flag to test against
 * if $incFlag = TRUE: returns Array ([0] => flag, [1] => text)
 * else returns text
 */
function check_flaglist($text, $compare, $incFlag = TRUE) {
	$result = '';

	foreach ($text AS $flag => $txt) {
		if (check_flag($flag, $compare)) {
			$result = $incFlag ? array($flag, $txt) : $txt;
		}
	}

	return $result === '' ? FALSE : $result;
}

/*
 * Simple array -> Replace mapping function
 * Ex: print map_replace(array('foo' => 'bar'), '{foo} stuff')
 * -> bar stuff
 */
function map_replace($map, $text) {
	foreach ($map AS $find => $replace) {
		$text = str_replace('{'.$find.'}', $replace, $text);
	}

	return $text;
}

/*
 * Cleans up string of all weird characters for use in URL
 */
function string2url($string) {
	$url = str_replace("'", '', $string);
	$url = str_replace('%20', ' ', $url);

	// Substitutes anything but letters, numbers and '_' with separator
	$url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
	$url = trim($url, '-');

	// You may opt for your own custom character map for encoding.
	$url = iconv('utf-8', 'us-ascii//TRANSLIT', $url);
	$url = strtolower($url);

	// Keep only letters, numbers, '_' and separator
	$url = preg_replace('~[^-a-z0-9_]+~', '', $url);

	return $url;
}

/*
 * Validate Passwords
 *
 * Restrictions:
 * Must be atleast 6 chars
 * Must have at least 1 letter
 * Must have at least 1 number
 */
function valid_pass($pass) {
	return preg_match('/^.*(?=.{6,})(?=.*\d)(?=.*[a-zA-Z]).*$/', $pass) === FALSE
			? FALSE : TRUE;
}

/*
 * Format phone number into standard format
 * No AC: 555-5102
 * With AC: (555) 555-3452
 */
function format_phone($phone) {
	$phone = preg_replace('/[^0-9]/', '', $phone);

	if (strlen($phone) === 7) {
		return preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $phone);
	}
	elseif (strlen($phone) === 10) {
		return preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/',
			'($1) $2-$3', $phone);
	}

	else return $phone;
}

/*
 * file_put_contents from PHP 5.3
 * for servers running older PHP versions
 */
if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data) {
		$f = @fopen($filename, 'w');
		if (!$f) return FALSE;
		else {
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}

/*
 * Delete file or directory recursively
 * Obviously requires proper permissions
 * **USE WITH CAUTION**
 */
function recursiveDelete($str) {
	if (is_file($str)) return unlink($str);
	elseif (is_dir($str)) {
		$scan = glob(rtrim($str, '/').'/*');

		foreach ($scan AS $index => $path) {
			recursiveDelete($path);
		}

		return rmdir($str);
	}
}

/*
 * Get lat/lng from google based on address
 * MAPS_HOST and MAPS_KEY are defined in config
 */
function google_latlng($addr, &$status) {
	if ($addr == '') return FALSE;

	// Set up variables
	$delay = 0;
	$base = 'http://'.MAPS_HOST.'/maps/geo?output=csv&key='.MAPS_KEY;
	$pending = TRUE;
	$status = '';

	while ($pending) {
		$request = $base.'&q='.urlencode($addr);
		$csv = file_get_contents($request);

		if (!$csv) $delay += 100000;
		else {
			$split = split(',',$csv);
			$status = $split[0];
			if (strcmp($status, '200') == 0) {
				// Return array(lat,lng,acc)
				return array($split[2],$split[3], (int) $split[1]);
			}
			elseif (strcmp($status,'620') == 0 || strcmp($status,'403') == 0) {
				$delay += 100000; // Sent geocodes too fast
			}
			else return FALSE; // Failure to geocode
		}

		usleep($delay);
	}

	// If it reaches this point, just move on
	return FALSE;
}

/*
 * Resize images being uploaded
 * $type: jpg,jpeg,png,gif
 * $orig: Original Image
 * $new: New image path/name
 * $size: Size of the image (determines how big the longest side will be)
 * $quality: Optional; default 75 (only works with jpegs)
 */
function img_resize($type, $orig, $new, $size, $quality = 75) {
	global $cfg;

	if ($orig === '') return FALSE;

	$type = strtolower($type);

	if ($type === 'jpg') $type = 'jpeg';
	if (array_search($type,$cfg['image_types']) === FALSE) return FALSE;

	$width = $size;
	$height = $size;

	list($width_orig,$height_orig) = getimagesize($orig);

	// If it doesn't need to be resized, copy original file
	if ($width_orig < $width && $height_orig < $height) {
		if (!copy($orig,$new)) return FALSE;
	}
	// Resize otherwise
	else {
		if ($width && ($width_orig < $height_orig)) {
			$width = ($height/$height_orig)*$width_orig;
		}
		else $height = ($width/$width_orig)*$height_orig;

		$image_p = imagecreatetruecolor($width,$height);
		$image = call_user_func('imagecreatefrom'.$type,$orig);
		imagecopyresampled($image_p,$image,0,0,0,0,$width,
							$height,$width_orig,$height_orig);

		if ($type === 'jpeg') {
			call_user_func('image'.$type,$image_p,$new,$quality);
		}
		else call_user_func('image'.$type,$image_p,$new);
	}

	chmod($new,0777); // Make sure it has proper permissions

	return TRUE;
}

/*
 * Generate random unique receipt #
 * based on ID of entry;
 * Ex: gen_receipt(24);
 * -> 00024-a0ce3dc4
 */
function gen_receipt($id) {
	return str_pad($id,5,'0',STR_PAD_LEFT).'-'.substr(sha1(uniqid('',TRUE)),0,8);
}

/*
 * ISO-81601 Date Format for PHP 4
 * PHP5: date('c')
 */
function iso_8601($t) {
	$z = date('O',$t);
	return date('Y-m-d\TH:i:s',$t).substr($z,0,3).':'.substr($z,3,2);
}

/*
 * Truncate String
 * Common ext: &#8230;
 */
function truncate($st, $max, $ext = '&#8230;') {
	// Set the replacement for the "string break" in the wordwrap function
	$marker = '&#133;';

	if (strlen($st) > $max) {
	   $st = explode($marker,wordwrap($st,$max,$marker,1));
	   $st = $st[0].$ext;
	}

	return $st;
}

/*
 * Convert bytes to human-readable number
 * $long = TRUE will produce full words (like Kilobytes)
 * By Default (FALSE) it returns 'MB' and 'KB'
 */
function bytes2num($bytes, $long = FALSE) {
	$size = $bytes/1024;

	if ($size < 1024) {
		$size = number_format($size, 2).' '.($long ? 'Kilobytes' : 'KB');
	}
	else  {
		if ($size / 1024 < 1024) {
			$size = number_format($size / 1024, 2).
					' '.($long ? 'Megabytes' : 'MB');
		}
		elseif ($size / 1024 / 1024 < 1024) {
			$size = number_format($size / 1024 / 1024, 2).
					' '.($long ? 'Gigabytes' : 'GB');
		}
		else {
			$size = number_format($bytes/1024).' '.($long ? 'Bytes' : 'B');
		}
	}

	return $size;
}

/*
 * Convert hour time (HH:MM:SS) to seconds
 * Ex: time2sec(01:55:22) -> 6922
 */
function time2secs($time) {
	$s = explode(':',$time);
	return (int) ((intval($s[0])*60)*60)+(intval($s[1])*60)+intval($s[2]);
}

/* Convert seconds to a string
 * Ex: secs2str(6922) -> 1hr, 55min, 22secs
 * Ex: secs2str(6922, TRUE) -> 1 hour, 55 minutes, 22 seconds
 */
function secs2str($secs, $long = FALSE) {
	// Reset hours, mins, and secs we'll be using
	$hours = 0;
	$mins = 0;
	$secs = $secs > 31536000 ? intval(NOW-$secs) : intval($secs);
	$t = array(); // Hold all 3 time periods to return as string

	// Take care of mins and left-over secs
	if ($secs >= 60) {
		$mins += floor($secs/60);
		$secs = (int)$secs%60;

		// Now handle hours and left-over mins
		if ($mins >= 60) {
			$hours += floor($mins/60);
			$mins = (int)$mins%60;
		}

		// We're done! now save time periods into our array
		if ($hours !== 0) $t['hours'] = $hours;
		$t['mins'] = $mins < 10 ? '0'.$mins : $mins;
	}

	// What's the final amount of secs?
	$t['secs'] = $secs < 10 ? '0'.$secs : $secs;

	// Decide how we should name hours, mins, sec
	$str_hours = $long === TRUE ? ' hour' : 'hr';
	$str_mins = $long === TRUE ? ' minute' : 'min';
	$str_secs = $long === TRUE ? ' second' : 'sec';

	// Hide number if 0
	if (intval($t['secs']) === 0) {
		unset($t['secs']);

		if (intval($t['mins']) === 0) unset($t['mins']);
	}

	// Build the pretty time string in an ugly way (pluralization and all that)
	$time_string  = isset($t['hours']) ? $t['hours'].$str_hours.
						(intval($t['hours']) === 1 ? '' : 's') : '';

	$time_string .= isset($t['mins']) ? (isset($t['hours']) ? ', ' : '') : '';

	$time_string .= isset($t['mins']) ? $t['mins'].$str_mins.
						(intval($t['mins']) === 1 ? '' : 's') : '';

	$time_string .= isset($t['secs']) ? ', '.$t['secs'].$str_secs.
						(intval($t['secs']) === 1 ? '' : 's') : '';

	return empty($time_string) ? 0 : $time_string;
}

/*
 * Calculates time since given timestamp
 * Written By: Frentic
 * Modified By: Steven Bower
 */
function time_since($time, $showdate = TRUE) {
	$secs = array(
		array(31536000,'year'),
		array(2592000,'month'),
		array(604800,'week'),
		array(86400,'day'),
		array(3600,'hour'),
		array(60,'minute'),
	);

	$since = NOW-$time;
	$str = '';

	if ($showdate === TRUE && $since > 604800) {
		$str = date('F jS',$time);
		if ($since > 31536000) $str .= ', '.date('Y',$time);

		return $str;
	}

	for ($i = 3; $i < 6; ++$i) {
		$name = $secs[$i][1];
		$num = (int) floor($since/$secs[$i][0]);

		if ($num !== 0) break;
	}

	return $num.' '.$name.($num === 1 ? '' : 's').' ago';
}

/*
 * Parse CSV File by Row
 */
function parse_csv_row($file, $longest = 0, $delimiter = ',') {
	if (!file_exists($file)) return FALSE;

	$data = array();
	$file = fopen($file, 'r');

	while (($line = fgetcsv($file, $longest, $delimiter)) !== FALSE) {
		array_push($data, $line);
	}

	fclose($file);

	return $data;
}

/*
 * Parse CSV file by column
 */
function parse_csv_col($file, $map, $longest = 0, $delimiter = ',') {
	if (!file_exists($file)) return FALSE;

	$data = array();
	$file = fopen($file,'r');

	$cnt = 0;
	while ($line = fgetcsv($file,$longest,$delimiter)) {
		if ($cnt == 0) {
			++$cnt;
			continue;
		}

		foreach ($map AS $key => $col) {
			if (isset($line[$key])) {
				// Set 0 as empty set
				if (!isset($data[$col])) $data[$col][0] = NULL;

				$data[$col][] = $line[$key];
			}
		}
	}

	fclose($file);

	return $data;
}

/*
 * Calculates distance in miles from one set
 * of coordinates to another
 */
function calculate_mileage($lat1, $lat2, $lon1, $lon2) {
	// Convert lattitude/longitude (degrees) to radians for calculations
	$lat1 = deg2rad($lat1);
	$lon1 = deg2rad($lon1);
	$lat2 = deg2rad($lat2);
	$lon2 = deg2rad($lon2);

	// Find the deltas
	$delta_lat = $lat2 - $lat1;
	$delta_lon = $lon2 - $lon1;

	// Find the Great Circle distance
	$temp = pow(sin($delta_lat/2.0),2) + cos($lat1)
			* cos($lat2) * pow(sin($delta_lon/2.0),2);
	$distance = 3956 * 2 * atan2(sqrt($temp),sqrt(1-$temp));

	return $distance;
}

/*
 * Cleans up string of all weird characters for use in URL
 */
function url_clean($string) {
    $url = str_replace("'", '', $string);
	$url = str_replace('%20', ' ', $url);

	// Substitutes anything but letters, numbers and '_' with separator
	$url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
	$url = trim($url, '-');

	// you may opt for your own custom character map for encoding.
	$url = iconv('utf-8', 'us-ascii//TRANSLIT', $url);
	$url = strtolower($url);

	 // keep only letters, numbers, '_' and separator
	$url = preg_replace('~[^-a-z0-9_]+~', '', $url);

	return $url;
}

/*
 * Sanitize characters from Windows-1252 (Microsoft Word)
 * Doesn't get all of them, but at least the major ones
 */
function sanitize($text = '') {
	$chars = array(
		128 => '&euro;', // Euro Sign
		130 => '&#39;', // baseline single quote
		131 => '&#402;', // florin
		132 => '&quot;', // baseline double quote
		133 => '&#8230;', // ellipsis
		134 => '&dagger;', // dagger (a second footnote)
		135 => '&Dagger;', // double dagger (a third footnote)
		136 => '&#94;', // circumflex accent
		137 => '&permil;', // permile
		138 => '&#352;', // capital letter S with caron
		139 => '&lsaquo;', // left single guillemet
		140 => '&#338;', // large OE ligature
		142 => 'Z', // Capital Z w/ caron
		145 => '&lsquo;', // left single quote
		146 => '&rsquo;', // right single quote
		147 => '&ldquo;', // left double quote
		148 => '&rdquo;', // right double quote
		149 => '&middot;', // bullet
		150 => '&ndash;', // endash
		151 => '&mdash;', // emdash
		152 => '~', // tilde accent
		153 => '&trade;', // trademark ligature
		154 => '&#353;', // small letter s with caron
		155 => '&rsaquo;', // right single guillemet
		156 => '&#339;', // small oe ligature
		158 => 'z', // Lowercase Z w/ caron
		159 => '&#376', // Y Dieresis
		161 => '!', // Exlamation
		162 => '&cent;', // Cent sign
		163 => '&pound;', // pound sterling
		164 => '&curren;', // Generic Current mark
		165 => '&yen;', // Yen
		166 => '&brvbar;', // Broken Bar (or &brkbar;)
		167 => '&sect;', // Section sign
		168 => '&die;', // Diaeresis (&die;) or umlaut (&uml;)
		169 => '&copy;', // Copyright
		170 => '&ordf;', // feminine ordinal
		171 => '&laquo;', // left angled double quote
		172 => '&not;', // Logic not
		173 => '&shy;', // Soft hyphen
		174 => '&reg;', // Registered Trademark
		175 => '&macr;', // Marcon &macr; or &hibar;
		176 => '&deg;', // Degree
		177 => '&plusmn;', // Plus-minus
		178 => '&sup2;', // Squared
		179 => '&sup3;', // Cubed
		180 => '&acute;', // Accute accent
		181 => '&micro;', // Micro sin
		182 => '&para;', // Paragraph
		183 => '&middot;', // Inner punctuation
		184 => '&cedil;', // Cedilla
		185 => '&sup1;', // Subscript 1
		186 => '&ordm;', // Masculine ordinal
		187 => '&raquo;', // right angled double quote
		188 => '&frac14;', // 1/4
		189 => '&frac12;', // 1/2
		190 => '&frac34;', // 3/4
		191 => '&iquest;', // Inverted question mark
	);

	foreach ($chars as $chr => $replace) {
		$text = str_replace(chr($chr),$replace,$text);
	}

	return $text;
}

/*
 * <TWCMS>
 *
 * Utility Arrays
 *
 * Useful arrays for common information
 * Kept here so not to clog up config file
 */

// HTTP Status codes and their proper messages
$cfg['httpCodes'] = array(
		// [Informational 1xx]
		100 => '100 Continue',
		101 => '101 Switching Protocols',

		// [Successful 2xx]
		200 => '200 OK',
		201 => '201 Created',
		202 => '202 Accepted',
		203 => '203 Non-Authoritative Information',
		204 => '204 No Content',
		205 => '205 Reset Content',
		206 => '206 Partial Content',

		// [Redirection 3xx]
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		304 => '304 Not Modified',
		305 => '305 Use Proxy',
		306 => '306 (Unused)',
		307 => '307 Temporary Redirect',

		// [Client Error 4xx]
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		402 => '402 Payment Requred',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		405 => '405 Method Not Allowed',
		406 => '406 Not Acceptable',
		407 => '407 Proxy Authentication Required',
		408 => '408 Request Timeout',
		409 => '409 Conflict',
		410 => '410 Gone',
		411 => '411 Length Required',
		412 => '412 Precondition Failed',
		413 => '413 Request Entity Too Large',
		414 => '414 Request-URI Too Long',
		415 => '415 Unsupported Media Type',
		416 => '416 Requested Range Not Satisfiable',
		417 => '417 Expectation Failed',

		// [Server Error 5xx]
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported'
);


// Array of US States
// Used for form fields and validation
// and for other display purposes
$cfg['states'] = array(
	'AL' => 'Alabama','AK' => 'Alaska','AZ' => 'Arizona',
	'AR' => 'Arkansas','CA' => 'California','CO' => 'Colorado',
	'CT' => 'Connecticut','DE' => 'Delaware','DC' => 'District Of Columbia',
	'FL' => 'Florida','GA' => 'Georgia','HI' => 'Hawaii','ID' => 'Idaho',
	'IL' => 'Illinois','IN' => 'Indiana','IA' => 'Iowa','KS' => 'Kansas',
	'KY' => 'Kentucky','LA' => 'Louisiana','ME' => 'Maine','MD' => 'Maryland',
	'MA' => 'Massachusetts','MI' => 'Michigan','MN' => 'Minnesota',
	'MS' => 'Mississippi','MO' => 'Missouri','MT' => 'Montana',
	'NE' => 'Nebraska','NV' => 'Nevada','NH' => 'New Hampshire',
	'NJ' => 'New Jersey','NM' => 'New Mexico','NY' => 'New York',
	'NC' => 'North Carolina','ND' => 'North Dakota','OH' => 'Ohio',
	'OK' => 'Oklahoma','OR' => 'Oregon','PA' => 'Pennsylvania',
	'RI' => 'Rhode Island','SC' => 'South Carolina','SD' => 'South Dakota',
	'TN' => 'Tennessee','TX' => 'Texas','UT' => 'Utah','VT' => 'Vermont',
	'VA' => 'Virginia','WA' => 'Washington','WV' => 'West Virginia',
	'WI' => 'Wisconsin','WY' => 'Wyoming'
);

// EOF
