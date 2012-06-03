<?php
/*
 * TWCMS <Module>
 *
 * Mod Version: 0.5
 * Author: Steven Bower
 * TurnWheel Designs (cc) 2012
 *
 * Useful form functions
 * Includes sending emails and saving to DB
 */

if (!defined('SECURITY')) exit;

/*
 * Process form data
 *
 * $errors -> Returns array of errors by ref
 */
function form_process($name, &$error) {
	global $cfg;

	// Make sure we have settings for this form
	if (!isset($cfg['forms'][$name])) return FALSE;

	// Form variables
	$fcfg = $cfg['forms'][$name];
	$data = array();
	$fields = $fcfg['fields'];
	$required = $fcfg['required'];

	// Get specified form fields
	foreach ($fields AS $field) {
		$data[$field] = isset($_POST[$field]) ?
			html_escape($_POST[$field]) : '';
	}

	// Check if form was even submitted at this point
	// If so, just return with existing data
	if (!isset($_POST['submit'])) return $data;

	// Verify required fields
	foreach ($required AS $field) {
		if (empty($data[$field]) || $data[$field] === 0) {
			$error[$field] = TRUE;
		}
	}

	// Verify email with special case
	if (isset($data['email']) && array_search('email', $required) !== FALSE
			&& !isset($error['email'])
			&& !valid_email($data['email'])) {
		$error['email'] = TRUE;
	}

	// If there are errors at this point, end processing
	if (sizeof($error) !== 0) return $data;

	// Save data to DB if enabled
	if ($fcfg['savedb'] && $cfg['sql_enable']) {
		// Process form data
		$sqldata = serialize($data);
		sql_query('INSERT INTO forms
					SET data = "%s", date = "%d", name = "%s"',
						array(serialize($data), NOW, $name));
	}

	/* Handle emailing */

	// Generate map of variables for email if needed
	$map = array();
	$ecfg = $fcfg['emails'];
	if ($ecfg['admin']['enable'] || $ecfg['user']['enable']) {
		// Escape data properly for use in emails
		foreach ($data AS $k => $v) {
			$map[$k] = html_escape(str_replace('\r\n', "\n", $v));
		}

		$map['date'] = date($fcfg['email_date'], NOW);
	}

	// Send Out User Email
	$ucfg = $ecfg['user'];
	if (isset($ucfg['enable']) && $ucfg['enable'] && isset($data[$ucfg['to']])) {
		$temp = str_replace("\t", '', $ucfg['body']);
		$body = map_replace($map, $temp);
		$subj = map_replace($map, $ucfg['subject']);
		$head = map_replace($map, $ucfg['headers']);

		mail($data[$ucfg['to']], $subj, $body, $head);
	}

	// Send Out Admin Email
	$acfg = $ecfg['admin'];
	if (isset($acfg['enable']) && $acfg['enable']) {
		$temp = str_replace("\t", '', $acfg['body']);
		$body = map_replace($map, $temp);
		$subj = map_replace($map, $acfg['subject']);
		$head = map_replace($map, $acfg['headers']);
		$to = implode(',', $acfg['to']);

		mail($to, $subj, $body, $head);
	}

	// Check for redirection
	if (is_string($fcfg['redirect'])) {
		header('Location: '.$fcfg['redirect']);
		exit;
	}

	return $data;
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
 * Validate EMail Address
 * Full rfc3696 valid function
 * is at the bottom of this file.
 */
function valid_email($email)  {
	return is_rfc822_valid_email_address($email);
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

// EOF
