<?php
/*
 * TurnWheel CMS
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
 * Root URL's into their full name
 */
function root_url2name($url) {
	return ucwords(str_replace('-',' / ', str_replace('_',' ',$url)));
}

/*
 * <TWCMS>
 * Check Bit Flags
 * Usage: check_flag(F_FLAG,$flag)
 */
function check_flag($flag,$val) {
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
function check_flaglist($text,$compare,$incFlag = TRUE) {
	$result = '';

	foreach ($text AS $flag => $txt) {
		if (check_flag($flag,$compare)) {
			$result = $incFlag ? array($flag,$txt) : $txt;
		}
	}

	return $result === '' ? FALSE : $result;
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
 * Validate EMail Address
 * Full rfc3696 valid function
 * is at the bottom of this file.
 */
function valid_email($email)  {
	return is_rfc3696_valid_email_address($email);
}

/*
 * Validate Phone Numbers
 * Supports formats:
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

#
# RFC3696 Email Parser
#
# By Cal Henderson <cal@iamcal.com>
#
# This code is dual licensed:
# CC Attribution-ShareAlike 2.5 - http://creativecommons.org/licenses/by-sa/2.5/
# GPLv3 - http://www.gnu.org/copyleft/gpl.html
#
# $Revision: 5039 $
#

###################################################################
function is_rfc3696_valid_email_address($email) {


	#############################################################################
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


	#############################################################################
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


	##############################################################################
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

	$outer_ccontent_dull = "(?:$fws?$ctext|$quoted_pair)";
	$outer_ccontent_nest = "(?:$fws?$comment)";
	$outer_comment = "(?:\\x28$outer_ccontent_dull*(?:$outer_ccontent_nest".
						"$outer_ccontent_dull*)+$fws?\\x29)";


	##############################################################################
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

	$atext		= "(?:$alpha|$digit|[\\x21\\x23-\\x27\\x2a\\x2b\\x2d".
					"\\x2f\\x3d\\x3f\\x5e\\x5f\\x60\\x7b-\\x7e])";
	$atom		= "(?:$cfws?(?:$atext)+$cfws?)";


	###############################################################################
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


	#############################################################################
	#
	# obs-local-part  =		  word *("." word)
	# obs-domain	  =		  atom *("." atom)

	$obs_local_part	= "(?:$word(?:\\x2e$word)*)";
	$obs_domain	= "(?:$atom(?:\\x2e$atom)*)";


	#############################################################################
	#
	# dot-atom-text   =		  1*atext *("." 1*atext)
	# dot-atom		  =		  [CFWS] dot-atom-text [CFWS]

	$dot_atom_text	= "(?:$atext+(?:\\x2e$atext+)*)";
	$dot_atom	= "(?:$cfws?$dot_atom_text$cfws?)";


	##############################################################################
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


	###############################################################################
	#
	# local-part	  =		  dot-atom / quoted-string / obs-local-part
	# domain		  =		  dot-atom / domain-literal / obs-domain
	# addr-spec		  =		  local-part "@" domain

	$local_part	= "(($dot_atom)|($quoted_string)|($obs_local_part))";
	$domain		= "(($dot_atom)|($domain_literal)|($obs_domain))";
	$addr_spec	= "$local_part\\x40$domain";



	#
	# see http://www.dominicsayers.com/isemail/ for details,
	# but this should probably be 254
	#

	if (strlen($email) > 256) return FALSE;


	#
	# we need to strip nested comments first -
	# we replace them with a simple comment
	#

	$email = rfc3696_strip_comments($outer_comment, $email, "(x)");


	#
	# now match what's left
	#

	if (!preg_match("!^$addr_spec$!", $email, $m)) {

		return FALSE;
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
	# since we know they're i the right place and we want them out of the
	# way for checking IPs, label sizes, etc
	#

	$bits['local']	= rfc3696_strip_comments($comment, $bits['local']);
	$bits['domain']	= rfc3696_strip_comments($comment, $bits['domain']);


	#
	# length limits on segments
	#

	if (strlen($bits['local']) > 64) return FALSE;
	if (strlen($bits['domain']) > 255) return FALSE;


	#
	# restrictuions on domain-literals from RFC2821 section 4.1.3
	#

	if (strlen($bits['domain-literal'])){

		$Snum = "(\d{1,3})";
		$IPv4_address_literal = "$Snum\.$Snum\.$Snum\.$Snum";

		$IPv6_hex = "(?:[0-9a-fA-F]{1,4})";

		$IPv6_full = "IPv6\:$IPv6_hex(:?\:$IPv6_hex){7}";

		$IPv6_comp_part = "(?:$IPv6_hex(?:\:$IPv6_hex){0,5})?";
		$IPv6_comp = "IPv6\:($IPv6_comp_part\:\:$IPv6_comp_part)";

		$IPv6v4_full = "IPv6\:$IPv6_hex(?:\:$IPv6_hex){5}\:$IPv4_address_literal";

		$IPv6v4_comp_part = "$IPv6_hex(?:\:$IPv6_hex){0,3}";
		$IPv6v4_comp = "IPv6\:((?:$IPv6v4_comp_part)?\:\:".
						"(?:$IPv6v4_comp_part\:)?)$IPv4_address_literal";


		#
		# IPv4 is simple
		#
		if (preg_match("!^\[$IPv4_address_literal\]$!", $bits['domain'], $m)) {
			if (intval($m[1]) > 255) return FALSE;
			if (intval($m[2]) > 255) return FALSE;
			if (intval($m[3]) > 255) return FALSE;
			if (intval($m[4]) > 255) return FALSE;
		}
		else {
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
					if (count($groups) > 6) return FALSE;
					break;
				}

				if (preg_match("!^\[$IPv6v4_full\]$!", $bits['domain'], $m)) {
					if (intval($m[1]) > 255) return FALSE;
					if (intval($m[2]) > 255) return FALSE;
					if (intval($m[3]) > 255) return FALSE;
					if (intval($m[4]) > 255) return FALSE;
					break;
				}

				if (preg_match("!^\[$IPv6v4_comp\]$!", $bits['domain'], $m)) {
					list($a, $b) = explode('::', $m[1]);

					# remove the trailing colon before the IPv4 address
					$b = substr($b, 0, -1);
					$folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
					$groups = explode(':', $folded);

					if (count($groups) > 4) return FALSE;
					break;
				}

				return FALSE;
			}
		}
	}
	else {

		#
		# the domain is either dot-atom or obs-domain - either way, it's
		# made up of simple labels and we split on dots
		#
		$labels = explode('.', $bits['domain']);

		#
		# this is allowed by both dot-atom and obs-domain, but is
		# un-routeable on the public internet, so we'll fail it
		# (e.g. user@localhost)
		#
		if (count($labels) == 1) return FALSE;

		#
		# checks on each label
		#
		foreach ($labels as $label) {
			if (strlen($label) > 63) return FALSE;
			if (substr($label, 0, 1) == '-') return FALSE;
			if (substr($label, -1) == '-') return FALSE;
		}

		#
		# last label can't be all numeric
		#
		if (preg_match('!^[0-9]+$!', array_pop($labels))) return FALSE;
	}


	return TRUE;
}

function rfc3696_strip_comments($comment, $email, $replace='') {
	while (1) {
		$new = preg_replace("!$comment!", $replace, $email);
		if (strlen($new) == strlen($email)){
			return $email;
		}
		$email = $new;
	}
}

// EOF
