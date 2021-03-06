<?php
/*
 * TWCMS 1.x
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
 * Usage: hasflag($flags, F_FLAG)
 */
function hasflag($val, $flag) {
	return (bool) ($val & $flag);
}

/*
 * Check list of flags for highest value
 * $text = array of (flag) => (text descrip)
 * $compare = flag setting to test against
 *
 * if $incFlag = TRUE: returns Array ([0] => flag, [1] => text)
 * else returns text descrip
 */
function hasflag_list($text, $compare, $incFlag = TRUE) {
	$result = '';

	foreach ($text AS $flag => $txt) {
		if (hasflag($compare, $flag)) {
			$result = $incFlag ? array($flag, $txt) : $txt;
		}
	}

	return $result === '' ? FALSE : $result;
}

/*
 * <TWCMS>
 * Adds specified flag to value
 * Ex: addflag(25, 4) -> 29
 * Ex: addflag(29, 4) -> 29
 */
function addflag($val, $flag) {
	return $val | $flag;
}

/*
 * <TWCMS>
 * Removes specified flag from value
 * Ex: rmflag(29, 4) -> 25
 * Ex: rmflag(25, 4) -> 25
 */
function rmflag($val, $flag) {
	return $val & ~$flag;
}

/*
 * <TWCMS>
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
	   $st = explode($marker, wordwrap($st, $max, $marker, 1));
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
		array(31536000, 'year'),
		array(2592000, 'month'),
		array(604800, 'week'),
		array(86400, 'day'),
		array(3600, 'hour'),
		array(60, 'minute'),
	);

	$since = NOW-$time;
	$str = '';

	if ($showdate === TRUE && $since > 604800) {
		$str = date('F jS', $time);
		if ($since > 31536000) $str .= ', '.date('Y', $time);

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
	if (!is_file($file)) return FALSE;

	$data = array();
	$file = fopen($file, 'r');

	while (($line = fgetcsv($file, $longest, $delimiter)) !== FALSE) {
		array_push($data, $line);
	}

	fclose($file);

	return $data;
}

/*
 * Parse CSV File by Column
 */
function parse_csv_col($file, $map, $longest = 0, $delimiter = ',') {
	if (!is_file($file)) return FALSE;

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
 * Get lat/lng from google based on address
 */
function google_latlng($addr) {
	if ($addr === '') return FALSE;

	$host = 'http://maps.googleapis.com';
	$base = $host.'/maps/api/geocode/json?sensor=false&address=';
	$url = $base.urlencode($addr);

	$data = file_get_contents($url);

	if (!$data) return FALSE;

	$json = json_decode($data, TRUE);

	// Verify status code
	$status = $json['status'];

	// If status is OK, return lat/lng information
	if ($json['status'] === 'OK') {
		$results = $json['results'][0];
		$loc = $results['geometry']['location'];
		return array($loc['lat'], $loc['lng']);
	}

	// If requests are happening to quickly, pause for a moment
	if ($json['status'] === 'OVER_QUERY_LIMIT') {
		usleep(500000);
		return FALSE;
	}

	return FALSE;
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
 * Validate Phone Numbers
 * Supported formats:
 * (555) 555-12345
 * 555-12345
 */
function valid_phone($pn, $useareacode = TRUE) {
	if ($pn === '') return FALSE;

	if (preg_match('/^[ ]*[(]{0,1}[ ]*[0-9]{3,3}[ ]*[)]{0,1}[-]{0,1}[ ]*[0-9]{3,3}'
			.'[ ]*[-]{0,1}[ ]*[0-9]{4,4}[ ]*$/',$pn)
		|| (!$useareacode &&
		preg_match('/^[ ]*[0-9]{3,3}[ ]*[-]{0,1}[ ]*[0-9]{4,4}[ ]*$/',$pn))) {
		return eregi_replace('[^0-9]', '', $pn);
	}

	return FALSE;
}

/*
 * Validate EMail Address
 * Wrapper for full rfc3696 valid function below
 * Forces return as bool
 */
function valid_email($email)  {
	return is_rfc822_valid_email_address($email) ? TRUE : FALSE;
}


#
# RFC 822/2822/5322 Email Parser
#
# By Cal Henderson <cal@iamcal.com>
#
# This code is dual licensed:
# CC Attribution-ShareAlike 2.5 - http://creativecommons.org/licenses/by-sa/2.5/
# GPLv3 - http://www.gnu.org/copyleft/gpl.html
#
# $Revision$
#

##################################################################################

function is_rfc822_valid_email_address($email, $options = array()) {

	#
	# you can pass a few different named options as a second argument,
	# but the defaults are usually a good choice.
	#

	$defaults = array(
		'allow_comments'	=> true,
		'public_internet'	=> true, # turn this off for 'strict' mode
	);

	$opts = array();
	foreach ($defaults as $k => $v) {
		$opts[$k] = isset($options[$k]) ? $options[$k] : $v;
	}
	$options = $opts;



	################################################################################
	#
	# NO-WS-CTL		  =		  %d1-8 /		  ; US-ASCII control characters
	#						  %d11 /		  ;  that do not include the
	#						  %d12 /		  ;  carriage return, line feed,
	#						  %d14-31 /		  ;  and white space characters
	#						  %d127
	# ALPHA			 =	%x41-5A / %x61-7A	; A-Z / a-z
	# DIGIT			 =	%x30-39

	$no_ws_ctl	= "[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]";
	$alpha		= "[\\x41-\\x5a\\x61-\\x7a]";
	$digit		= "[\\x30-\\x39]";
	$cr		= "\\x0d";
	$lf		= "\\x0a";
	$crlf		= "(?:$cr$lf)";


	################################################################################
	#
	# obs-char		  =		  %d0-9 / %d11 /		  ; %d0-127 except CR and
	#						  %d12 / %d14-127		  ;  LF
	# obs-text		  =		  *LF *CR *(obs-char *LF *CR)
	# text			  =		  %d1-9 /		  ; Characters excluding CR and LF
	#						  %d11 /
	#						  %d12 /
	#						  %d14-127 /
	#						  obs-text
	# obs-qp		  =		  "\" (%d0-127)
	# quoted-pair	  =		  ("\" text) / obs-qp

	$obs_char	= "[\\x00-\\x09\\x0b\\x0c\\x0e-\\x7f]";
	$obs_text	= "(?:$lf*$cr*(?:$obs_char$lf*$cr*)*)";
	$text		= "(?:[\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f]|$obs_text)";

	#
	# there's an issue with the definition of 'text', since 'obs_text' can
	# be blank and that allows qp's with no character after the slash. we're
	# treating that as bad, so this just checks we have at least one
	# (non-CRLF) character
	#

	$text		= "(?:$lf*$cr*$obs_char$lf*$cr*)";
	$obs_qp		= "(?:\\x5c[\\x00-\\x7f])";
	$quoted_pair	= "(?:\\x5c$text|$obs_qp)";


	################################################################################
	#
	# obs-FWS		  =		  1*WSP *(CRLF 1*WSP)
	# FWS			  =		  ([*WSP CRLF] 1*WSP) /   ; Folding white space
	#						  obs-FWS
	# ctext			  =		  NO-WS-CTL /	  ; Non white space controls
	#						  %d33-39 /		  ; The rest of the US-ASCII
	#						  %d42-91 /		  ;  characters not including "(",
	#						  %d93-126		  ;  ")", or "\"
	# ccontent		  =		  ctext / quoted-pair / comment
	# comment		  =		  "(" *([FWS] ccontent) [FWS] ")"
	# CFWS			  =		  *([FWS] comment) (([FWS] comment) / FWS)

	#
	# note: we translate ccontent only partially to avoid an infinite loop
	# instead, we'll recursively strip *nested* comments before processing
	# the input. that will leave 'plain old comments' to be matched during
	# the main parse.
	#

	$wsp		= "[\\x20\\x09]";
	$obs_fws	= "(?:$wsp+(?:$crlf$wsp+)*)";
	$fws		= "(?:(?:(?:$wsp*$crlf)?$wsp+)|$obs_fws)";
	$ctext		= "(?:$no_ws_ctl|[\\x21-\\x27\\x2A-\\x5b\\x5d-\\x7e])";
	$ccontent	= "(?:$ctext|$quoted_pair)";
	$comment	= "(?:\\x28(?:$fws?$ccontent)*$fws?\\x29)";
	$cfws		= "(?:(?:$fws?$comment)*(?:$fws?$comment|$fws))";


	#
	# these are the rules for removing *nested* comments. we'll just detect
	# outer comment and replace it with an empty comment, and recurse until
	# we stop.
	#

	$outer_ccontent_dull	= "(?:$fws?$ctext|$quoted_pair)";
	$outer_ccontent_nest	= "(?:$fws?$comment)";
	$outer_comment		= "(?:\\x28$outer_ccontent_dull*(?:$outer_ccontent_nest".
								"$outer_ccontent_dull*)+$fws?\\x29)";


	################################################################################
	#
	# atext			  =		  ALPHA / DIGIT / ; Any character except controls,
	#						  "!" / "#" /	  ;  SP, and specials.
	#						  "$" / "%" /	  ;  Used for atoms
	#						  "&" / "'" /
	#						  "*" / "+" /
	#						  "-" / "/" /
	#						  "=" / "?" /
	#						  "^" / "_" /
	#						  "`" / "{" /
	#						  "|" / "}" /
	#						  "~"
	# atom			  =		  [CFWS] 1*atext [CFWS]

	$atext		= "(?:$alpha|$digit|[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2f\\x3d\\x3f"
						."\\x5e\\x5f\\x60\\x7b-\\x7e])";
	$atom		= "(?:$cfws?(?:$atext)+$cfws?)";


	################################################################################
	#
	# qtext			  =		  NO-WS-CTL /	  ; Non white space controls
	#						  %d33 /		  ; The rest of the US-ASCII
	#						  %d35-91 /		  ;  characters not including "\"
	#						  %d93-126		  ;  or the quote character
	# qcontent		  =		  qtext / quoted-pair
	# quoted-string   =		  [CFWS]
	#						  DQUOTE *([FWS] qcontent) [FWS] DQUOTE
	#						  [CFWS]
	# word			  =		  atom / quoted-string

	$qtext		= "(?:$no_ws_ctl|[\\x21\\x23-\\x5b\\x5d-\\x7e])";
	$qcontent	= "(?:$qtext|$quoted_pair)";
	$quoted_string	= "(?:$cfws?\\x22(?:$fws?$qcontent)*$fws?\\x22$cfws?)";

	#
	# changed the '*' to a '+' to require that quoted strings are not empty
	#

	$quoted_string	= "(?:$cfws?\\x22(?:$fws?$qcontent)+$fws?\\x22$cfws?)";
	$word		= "(?:$atom|$quoted_string)";


	################################################################################
	#
	# obs-local-part  =		  word *("." word)
	# obs-domain	  =		  atom *("." atom)

	$obs_local_part	= "(?:$word(?:\\x2e$word)*)";
	$obs_domain	= "(?:$atom(?:\\x2e$atom)*)";


	################################################################################
	#
	# dot-atom-text   =		  1*atext *("." 1*atext)
	# dot-atom		  =		  [CFWS] dot-atom-text [CFWS]

	$dot_atom_text	= "(?:$atext+(?:\\x2e$atext+)*)";
	$dot_atom	= "(?:$cfws?$dot_atom_text$cfws?)";


	################################################################################
	#
	# domain-literal  =		  [CFWS] "[" *([FWS] dcontent) [FWS] "]" [CFWS]
	# dcontent		  =		  dtext / quoted-pair
	# dtext			  =		  NO-WS-CTL /	  ; Non white space controls
	#
	#						  %d33-90 /		  ; The rest of the US-ASCII
	#						  %d94-126		  ;  characters not including "[",
	#										  ;  "]", or "\"

	$dtext		= "(?:$no_ws_ctl|[\\x21-\\x5a\\x5e-\\x7e])";
	$dcontent	= "(?:$dtext|$quoted_pair)";
	$domain_literal	= "(?:$cfws?\\x5b(?:$fws?$dcontent)*$fws?\\x5d$cfws?)";


	################################################################################
	#
	# local-part	  =		  dot-atom / quoted-string / obs-local-part
	# domain		  =		  dot-atom / domain-literal / obs-domain
	# addr-spec		  =		  local-part "@" domain

	$local_part	= "(($dot_atom)|($quoted_string)|($obs_local_part))";
	$domain		= "(($dot_atom)|($domain_literal)|($obs_domain))";
	$addr_spec	= "$local_part\\x40$domain";



	#
	# this was previously 256 based on RFC3696, but dominic's errata was accepted.
	#

	if (strlen($email) > 254) return 0;


	#
	# we need to strip nested comments first - we replace them with a simple comment
	#

	if ($options['allow_comments']){

		$email = rfc822_strip_comments($outer_comment, $email, "(x)");
	}


	#
	# now match what's left
	#

	if (!preg_match("!^$addr_spec$!", $email, $m)){

		return 0;
	}

	$bits = array(
		'local'			=> isset($m[1]) ? $m[1] : '',
		'local-atom'		=> isset($m[2]) ? $m[2] : '',
		'local-quoted'		=> isset($m[3]) ? $m[3] : '',
		'local-obs'		=> isset($m[4]) ? $m[4] : '',
		'domain'		=> isset($m[5]) ? $m[5] : '',
		'domain-atom'		=> isset($m[6]) ? $m[6] : '',
		'domain-literal'	=> isset($m[7]) ? $m[7] : '',
		'domain-obs'		=> isset($m[8]) ? $m[8] : '',
	);


	#
	# we need to now strip comments from $bits[local] and $bits[domain],
	# since we know they're in the right place and we want them out of the
	# way for checking IPs, label sizes, etc
	#

	if ($options['allow_comments']){
		$bits['local']	= rfc822_strip_comments($comment, $bits['local']);
		$bits['domain']	= rfc822_strip_comments($comment, $bits['domain']);
	}


	#
	# length limits on segments
	#

	if (strlen($bits['local']) > 64) return 0;
	if (strlen($bits['domain']) > 255) return 0;


	#
	# restrictions on domain-literals from RFC2821 section 4.1.3
	#
	# RFC4291 changed the meaning of :: in IPv6 addresses - i can mean one or
	# more zero groups (updated from 2 or more).
	#

	if (strlen($bits['domain-literal'])){

		$Snum			= "(\d{1,3})";
		$IPv4_address_literal	= "$Snum\.$Snum\.$Snum\.$Snum";

		$IPv6_hex		= "(?:[0-9a-fA-F]{1,4})";

		$IPv6_full		= "IPv6\:$IPv6_hex(?:\:$IPv6_hex){7}";

		$IPv6_comp_part		= "(?:$IPv6_hex(?:\:$IPv6_hex){0,7})?";
		$IPv6_comp		= "IPv6\:($IPv6_comp_part\:\:$IPv6_comp_part)";

		$IPv6v4_full		= "IPv6\:$IPv6_hex(?:\:$IPv6_hex){5}\:".
								$IPv4_address_literal;

		$IPv6v4_comp_part	= "$IPv6_hex(?:\:$IPv6_hex){0,5}";
		$IPv6v4_comp		= "IPv6\:((?:$IPv6v4_comp_part)?\:\:(?:".
								"$IPv6v4_comp_part\:)?)$IPv4_address_literal";


		#
		# IPv4 is simple
		#

		if (preg_match("!^\[$IPv4_address_literal\]$!", $bits['domain'], $m)) {

			if (intval($m[1]) > 255) return 0;
			if (intval($m[2]) > 255) return 0;
			if (intval($m[3]) > 255) return 0;
			if (intval($m[4]) > 255) return 0;

		} else {

			#
			# this should be IPv6 - a bunch of tests are needed here :)
			#

			while (1) {

				if (preg_match("!^\[$IPv6_full\]$!", $bits['domain'])) {
					break;
				}

				if (preg_match("!^\[$IPv6_comp\]$!", $bits['domain'], $m)) {
					list($a, $b) = explode('::', $m[1]);
					$folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
					$groups = explode(':', $folded);
					if (count($groups) > 7) return 0;
					break;
				}

				if (preg_match("!^\[$IPv6v4_full\]$!", $bits['domain'], $m)) {

					if (intval($m[1]) > 255) return 0;
					if (intval($m[2]) > 255) return 0;
					if (intval($m[3]) > 255) return 0;
					if (intval($m[4]) > 255) return 0;
					break;
				}

				if (preg_match("!^\[$IPv6v4_comp\]$!", $bits['domain'], $m)) {
					list($a, $b) = explode('::', $m[1]);
					# remove the trailing colon before the IPv4 address
					$b = substr($b, 0, -1);
					$folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
					$groups = explode(':', $folded);
					if (count($groups) > 5) return 0;
					break;
				}

				return 0;
			}
		}
	} else {

		#
		# the domain is either dot-atom or obs-domain - either way, it's
		# made up of simple labels and we split on dots
		#

		$labels = explode('.', $bits['domain']);


		#
		# this is allowed by both dot-atom and obs-domain, but is un-routeable on the
		# public internet, so we'll fail it (e.g. user@localhost)
		#

		if ($options['public_internet']){
			if (count($labels) == 1) return 0;
		}


		#
		# checks on each label
		#

		foreach ($labels as $label){

			if (strlen($label) > 63) return 0;
			if (substr($label, 0, 1) == '-') return 0;
			if (substr($label, -1) == '-') return 0;
		}


		#
		# last label can't be all numeric
		#

		if ($options['public_internet']){
			if (preg_match('!^[0-9]+$!', array_pop($labels))) return 0;
		}
	}


	return 1;
}

##################################################################################

function rfc822_strip_comments($comment, $email, $replace=''){

	while (1){
		$new = preg_replace("!$comment!", $replace, $email);
		if (strlen($new) == strlen($email)){
			return $email;
		}
		$email = $new;
	}
}

/*
 * <TWCMS>
 *
 * Utility Arrays
 *
 * Useful arrays for common information
 * Kept here so we don't clog up config file
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
// Used mainly for form fields and validation
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

// Array of Countries
// Used mainly for form fields and validation
$cfg['countries'] = array(
	'AF' => 'Afghanistan',
	'AL' => 'Albania',
	'DZ' => 'Algeria',
	'AS' => 'American Samoa',
	'AD' => 'Andorra',
	'AO' => 'Angola',
	'AI' => 'Anguilla',
	'AG' => 'Antigua And Barbuda',
	'AR' => 'Argentina',
	'AM' => 'Armenia',
	'AW' => 'Aruba',
	'AU' => 'Australia',
	'AT' => 'Austria',
	'AZ' => 'Azerbaijan',
	'BS' => 'Bahamas',
	'BH' => 'Bahrain',
	'BD' => 'Bangladesh',
	'BB' => 'Barbados',
	'BY' => 'Belarus',
	'BE' => 'Belgium',
	'BZ' => 'Belize',
	'BJ' => 'Benin',
	'BM' => 'Bermuda',
	'BT' => 'Bhutan',
	'BO' => 'Bolivia',
	'BA' => 'Bosnia And Herzegovina',
	'BW' => 'Botswana',
	'BR' => 'Brazil',
	'IO' => 'British Indian Ocean Territory',
	'BN' => 'Brunei',
	'BG' => 'Bulgaria',
	'BF' => 'Burkina Faso',
	'BI' => 'Burundi',
	'KH' => 'Cambodia',
	'CM' => 'Cameroon',
	'CA' => 'Canada',
	'CV' => 'Cape Verde',
	'KY' => 'Cayman Islands',
	'CF' => 'Central African Republic',
	'TD' => 'Chad',
	'CL' => 'Chile',
	'CN' => 'China',
	'CO' => 'Colombia',
	'CG' => 'Congo',
	'CK' => 'Cook Islands',
	'CR' => 'Costa Rica',
	'CI' => 'Cote D\'ivoire',
	'HR' => 'Croatia',
	'CU' => 'Cuba',
	'CY' => 'Cyprus',
	'CZ' => 'Czech Republic',
	'CD' => 'Democratic Republic of the Congo',
	'DK' => 'Denmark',
	'DJ' => 'Djibouti',
	'DM' => 'Dominica',
	'DO' => 'Dominican Republic',
	'EC' => 'Ecuador',
	'EG' => 'Egypt',
	'SV' => 'El Salvador',
	'GQ' => 'Equatorial Guinea',
	'ER' => 'Eritrea',
	'EE' => 'Estonia',
	'ET' => 'Ethiopia',
	'FO' => 'Faroe Islands',
	'FM' => 'Federated States Of Micronesia',
	'FJ' => 'Fiji',
	'FI' => 'Finland',
	'FR' => 'France',
	'GF' => 'French Guiana',
	'PF' => 'French Polynesia',
	'GA' => 'Gabon',
	'GM' => 'Gambia',
	'GE' => 'Georgia',
	'DE' => 'Germany',
	'GH' => 'Ghana',
	'GI' => 'Gibraltar',
	'GR' => 'Greece',
	'GL' => 'Greenland',
	'GD' => 'Grenada',
	'GP' => 'Guadeloupe',
	'GU' => 'Guam',
	'GT' => 'Guatemala',
	'GN' => 'Guinea',
	'GW' => 'Guinea Bissau',
	'GY' => 'Guyana',
	'HT' => 'Haiti',
	'HN' => 'Honduras',
	'HK' => 'Hong Kong',
	'HU' => 'Hungary',
	'IS' => 'Iceland',
	'IN' => 'India',
	'ID' => 'Indonesia',
	'IR' => 'Iran',
	'IE' => 'Ireland',
	'IL' => 'Israel',
	'IT' => 'Italy',
	'JM' => 'Jamaica',
	'JP' => 'Japan',
	'JO' => 'Jordan',
	'KZ' => 'Kazakhstan',
	'KE' => 'Kenya',
	'KW' => 'Kuwait',
	'KG' => 'Kyrgyzstan',
	'LA' => 'Laos',
	'LV' => 'Latvia',
	'LB' => 'Lebanon',
	'LS' => 'Lesotho',
	'LY' => 'Libyan Arab Jamahiriya',
	'LI' => 'Liechtenstein',
	'LT' => 'Lithuania',
	'LU' => 'Luxembourg',
	'MK' => 'Macedonia',
	'MG' => 'Madagascar',
	'MW' => 'Malawi',
	'MY' => 'Malaysia',
	'MV' => 'Maldives',
	'ML' => 'Mali',
	'MT' => 'Malta',
	'MQ' => 'Martinique',
	'MR' => 'Mauritania',
	'MU' => 'Mauritius',
	'MX' => 'Mexico',
	'MC' => 'Monaco',
	'MN' => 'Mongolia',
	'ME' => 'Montenegro',
	'MA' => 'Morocco',
	'MZ' => 'Mozambique',
	'MM' => 'Myanmar',
	'NA' => 'Namibia',
	'NP' => 'Nepal',
	'NL' => 'Netherlands',
	'AN' => 'Netherlands Antilles',
	'NC' => 'New Caledonia',
	'NZ' => 'New Zealand',
	'NI' => 'Nicaragua',
	'NE' => 'Niger',
	'NG' => 'Nigeria',
	'NF' => 'Norfolk Island',
	'MP' => 'Northern Mariana Islands',
	'NO' => 'Norway',
	'OM' => 'Oman',
	'PK' => 'Pakistan',
	'PW' => 'Palau',
	'PA' => 'Panama',
	'PG' => 'Papua New Guinea',
	'PY' => 'Paraguay',
	'PE' => 'Peru',
	'PH' => 'Philippines',
	'PL' => 'Poland',
	'PT' => 'Portugal',
	'PR' => 'Puerto Rico',
	'QA' => 'Qatar',
	'MD' => 'Republic Of Moldova',
	'RE' => 'Reunion',
	'RO' => 'Romania',
	'RU' => 'Russia',
	'RW' => 'Rwanda',
	'KN' => 'Saint Kitts And Nevis',
	'LC' => 'Saint Lucia',
	'VC' => 'Saint Vincent And The Grenadines',
	'WS' => 'Samoa',
	'SM' => 'San Marino',
	'ST' => 'Sao Tome And Principe',
	'SA' => 'Saudi Arabia',
	'SN' => 'Senegal',
	'RS' => 'Serbia',
	'SC' => 'Seychelles',
	'SG' => 'Singapore',
	'SK' => 'Slovakia',
	'SI' => 'Slovenia',
	'SB' => 'Solomon Islands',
	'ZA' => 'South Africa',
	'KR' => 'South Korea',
	'ES' => 'Spain',
	'LK' => 'Sri Lanka',
	'SD' => 'Sudan',
	'SR' => 'Suriname',
	'SZ' => 'Swaziland',
	'SE' => 'Sweden',
	'CH' => 'Switzerland',
	'SY' => 'Syrian Arab Republic',
	'TW' => 'Taiwan',
	'TJ' => 'Tajikistan',
	'TZ' => 'Tanzania',
	'TH' => 'Thailand',
	'TG' => 'Togo',
	'TO' => 'Tonga',
	'TT' => 'Trinidad And Tobago',
	'TN' => 'Tunisia',
	'TR' => 'Turkey',
	'TM' => 'Turkmenistan',
	'UG' => 'Uganda',
	'UA' => 'Ukraine',
	'AE' => 'United Arab Emirates',
	'GB' => 'United Kingdom',
	'US' => 'United States',
	'UY' => 'Uruguay',
	'UZ' => 'Uzbekistan',
	'VU' => 'Vanuatu',
	'VE' => 'Venezuela',
	'VN' => 'Vietnam',
	'VG' => 'Virgin Islands British',
	'VI' => 'Virgin Islands U.S.',
	'YE' => 'Yemen',
	'ZM' => 'Zambia',
	'ZW' => 'Zimbabwe'
);

// EOF
