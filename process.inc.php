<?php
/*
 * TWCMS 1.x
 * Page Processing
 *
 * This file processes page headers and then searches
 * the content directory (CPATH) for the appropriate page.
 *
 * Returns template variables such as header, title, content,
 * all within the $T global array.
 *
 * Additional processing details can be found within
 * the $P global array.
 *
 * The main template is mainly responsible for parsing
 * and displaying these variables. Though they are also
 * accessible by dynamic content files (CPATH/*.inc.php)
 */

/*
 * Header Variable
 * All header values are stored here for quick access
 * Stored in # => val format for all headers
 * plus special vars (/cat:5/); cat => 5
 * pure integers are saved as id (/10/); id => 10,
 * additional integers get numbers: (/10/15/); id => 10, id2 => 15
 */
$H = array();

// Process Variable
// All values key to processing will be in the $P array.
$P = array();

// Define current URL as a constant without strings and achors
// Dumb-ass work-around because PHP doesn't like colons in parse_url
$url = str_replace(':', ';', REQUESTURL);
$path = parse_url($url, PHP_URL_PATH);
$path = str_replace(';', ':', $path);
define('CURRURL', $path);

// Run event 'beforeProcess'
tw_event('beforeProcess');

// Special case for index as default
if (CURRURL === '/') $headers = array('index');
else $headers = explode('/', CURRURL);

// Generate $H array based on headers
$lastid = 1;
foreach ($headers AS $k => $val) {
	if ($val === '' || $val === '/') continue;

	$H[] = path_escape($val);

	// If value is a pure integer
	// save as specific ID #
	if (intval($val) !== 0) {
		// Remove ID from main array
		array_pop($H);

		// Process which ID it should be
		$val = intval($val);
		if ($lastid === 1) {
			$H['id'] = $val;
		}
		else {
			$H['id'+$lastid] = $val;
		}

		++$lastid;
	}
	// If : is present, separate for variable
	// to use as key (cat:5); cat => 5
	elseif (strpos($val, ':') !== FALSE) {
		// Remove var from main array
		array_pop($H);

		// Split value based on colon
		$colon = explode(':', $val, 2);
		$ckey = $colon[0];
		$cval = $colon[1];

		// Save as associative array,
		// but also check if it is an integer.
		// Integers should always remain their type
		$H[$ckey] = intval($cval) !== 0 ? intval($cval) : path_escape($cval);
	}
}

/*
 * Get Raw Header Variables
 * GET variable can override normal URL variables
 * Example: /store/cat:5/?cat=10; $H['cat'] = 10
 */
foreach ($_GET AS $key => $val) {
	$H[$key] = $val;
}

// Determine which headers belong where
$P['root'] = ''; // Main page (first root in the chain)
$P['pages'] = array(); // Array of all pages

foreach ($H AS $key => $val) {
	// Exclude special variables
	if (!is_int($key)) continue;

	// Set root page (first name found)
	if ($key === 0) $P['root'] = $H[$key];

	$P['pages'][] = $H[$key];
}

// For tracking breadcrumbs
// (format: <title> => $url)
$T['bcrumbs'] = array('Home' => FULLURL);

// Generate starting breadcrumbs if not on home page
if ($P['root'] !== 'index') {
	// Add breadcrumbs for current page + subpages
	$prev = '';
	foreach ($P['pages'] AS $bcpage) {
		// Make titles look nice (space and captialize)
		// Use $prev to track previous url's
		$T['bcrumbs'][p_url2name($bcpage)] = $prev = $prev.'/'.$bcpage;
	}
}

/** Start file tracking **/
// Full page string using _ format (used for file lookup)
$P['page'] = implode('_', $P['pages']);

// Full page URL based on pages array
// No beginning '/', and no variables
$P['pageurl'] = implode('/', $P['pages']);

$P['404'] = FALSE; // Bool to determine if a 404 error needs to be thrown
$P['php'] = FALSE; // Determines if include file is PHP or HTML
$P['file'] = CPATH.$P['page']; // Full path to page file
$P['num'] = sizeof($P['pages']); // Number of sub-pages requested (nesting #)

/*
 * This checks for a .html file in CPATH;
 * If .html is not found, it looks for .inc.php
 * If not found, it will go up the path tree until
 * it finds any file it can.
 */
$notFound = TRUE;
while ($notFound) {
	// Makes sure that file exists AND is readable
	if (is_readable($P['file'].'.html')) {
		$P['file'] .= '.html';
		$notFound = FALSE;
	}
	elseif (is_readable($P['file'].'.inc.php')) {
		$P['file'] .= '.inc.php';
		$P['php'] = TRUE;
		$notFound = FALSE;
	}

	// If tryParents is disabled, just end here
	if (!$cfg['p_tryParents']) break;

	// If still not found, try to load parent page
	if ($notFound) {
		// Remove last ending to find parent file
		$split = explode('_', $P['file']);
		$sizeof = sizeof($split);

		// Break out of loop, file still not found
		if ($sizeof === 1) break;

		// Create new file string
		array_pop($split);
		$P['file'] = implode('_', $split);
	}
}

// Set 404 error if file is never found
if ($notFound) $P['404'] = TRUE;

// HARDCODED EXCEPTIONS
// 'sidebar' and 'error' are protected roots
// and used for internal files. These files are
// not accessible directly.
$stest = explode('.', $P['root'], 2);
if ($stest[0] === 'error' || $stest[0] === 'sidebar') {
	$P['404'] = TRUE;
}

// Is this the index page? (Bool)
// 'indexnew' is hardcoded here, so you can have a 'non-public index'
// that still behaves like the real index page
define('ISINDEX', ($P['page'] === 'index' || $P['page'] === 'indexnew'));

// Run module event 'beforeContent'
tw_event('beforeContent');

// If 404 flag, use 404 functions
if ($P['404']) {
	if (!p_showerror(404)) {
		print 'Error 404 Times TWO: A 404 error occured, then the 404'.
			'document could not be found. Please contact the administrator!';
		exit;
	}
}
elseif ($P['php']) {
	/*
	 * Set the variables allowed by scripts
	 * These 3 variables should be over-ridden in the .inc.php scripts
	 * Other than that, these scripts can do anything.
	 * Including requesting authentication, accepting post data, etc.
	 * This simply locks you into the default layout provided
	 */

	// Sets the main header in template content area
	$T['header'] = '';

	// Used for <title> in template
	// Usually same as header, but may be different in some cases
	$T['title'] = '';

	// Actual content put into main content div
	$T['content'] = '';

	// $P['file'] is include safe
	// See path_escape in lib/security.inc.php
	include $P['file'];
}
else {
	/*
	 * Handle .html files
	 * NOTE: The first line of every .html file becomes the header
	 * and is stripped of all html
	 */

	// p_htmlfile returns array with header and content
	// $file is include safe
	$html = p_htmlfile($P['file']);

	if (!$html && !p_showerror(404)) {
		// This should really never happen
		print 'Error 404 Times TWO: A 404-1 error occured, then the 404'.
			'document could not be found. Please contact the administrator!';
		exit;
	}

	$T['content'] = $html['content'];
	$T['title'] = $T['header'] = $html['header'];
}

// Run module event 'duringProcess'
tw_event('duringProcess');

// Swap bread crumbs for full title (only if this isnt already set)
if ($T['header']  !== '' && $P['page'] !== strtolower($T['header'])) {
	foreach ($T['bcrumbs'] AS $name => $url) {
		// Locate pre-set header and remove
		// Comparisions: /page === /page; /page/ === /page/
		// /page/ === /page.'/'; /page === /page/
		if ($url === CURRURL || $url === CURRURL.'/' ||
				$url === substr(CURRURL, 0, -1)) {
			unset($T['bcrumbs'][$name]);
		}
	}

	// Set new header for this URL (if not already set)
	if (!isset($T['bcrumbs'][$T['header']])) {
		$T['bcrumbs'][$T['header']] = CURRURL;
	}
}

// Add previous pages to title format
// make sure to exclude the current page
// which should already be set
if ($cfg['p_crumbTitles'] && $T['title'] !== '') {
	$tpages = array(); // Array to hold formatted title pages
	$revpages = array_slice(array_reverse($P['pages']), 1);

	foreach ($revpages AS $val) {
		// Skip array values to prevent errors and recursion
		if (is_array($val)) continue;

		$tpages[] = p_url2name($val);
	}

	// Add to title (make sure its not empty)
	if (sizeof($tpages) > 0) {
		$T['title'] .= ' :: '.implode(' > ',$tpages);
	}
}

// Remove any left-over characters from title
$T['title'] = str_replace("\r",'',str_replace("\n",'',trim($T['title'])));

// Exception for indexnew (dev/debug)
// this allows indexnew to load the normal index page resources
if ($P['page'] === 'indexnew') {
	$P['root'] = $P['page'] = 'index';
}

/*
 * Figure out which sidebar to load
 * Possible options:
 * sidebar.<rootpage>.(html|inc.php)
 * sidebar.default.(html|inc.php)
 */
$sb = 'sidebar.'; // String justs helps make the lines smaller
$T['sidebar'] =
	(is_file(CPATH.$sb.$P['root'].'.inc.php') ? CPATH.$sb.$P['root'].'.inc.php' :
	(is_file(CPATH.$sb.$P['root'].'.html') ? CPATH.$sb.$P['root'].'.html' :
	(is_file(CPATH.$sb.'default.inc.php') ? CPATH.$sb.'default.inc.php' :
	(is_file(CPATH.$sb.'default.html') ? CPATH.$sb.'default.html' : ''))));

/* Add Resources  */

// Default files
t_addRes('css', 'global', 2);
t_addRes('js', 'global', 2);

// Load subpage resources
if (!ISINDEX) {
	t_addRes('css', 'subpage', 2);
	t_addRes('js', 'subpage', 2);
}

// Load root page resources
// if checking parent, or if there is no parent
if ($cfg['res_checkRoot'] || $P['root'] === $P['page']) {
	t_addRes('css', $P['root'], 2);
	t_addRes('js', $P['root'], 2);
}

// Check for current page resources
// if current page different from root
if ($P['root'] !== $P['page']) {
	t_addRes('css', $P['page'], 2);
	t_addRes('js', $P['page'], 2);
}

/*
 * Recursive Resource Checks
 * (Applies to both css & js)
 * First Make sure environment is sane
 * 1: Check if enabled
 * 2: Check if on a subpage
 * 3: Not if res_checkRoot is TRUE && num pages is 2
 */
if ($cfg['res_recursive']) {
	if ($P['root'] !== $P['page'] &&
			!($cfg['res_checkRoot'] && $P['num'] === 2)) {
		// Check all pages
		$track = array();
		foreach ($P['pages'] AS $k => $cp) {
			$track[] = $cp;

			// Skip current page (last item)
			// and rootpage if checkRoot is TRUE (first item)
			if ($k === ($P['num']-1) || ($cfg['res_checkRoot'] && $k === 0)) {
				continue;
			}

			$curr = implode($track, '_');
			t_addRes('css', $curr, 3);
			t_addRes('js', $curr, 3);
		}
	}
}

// Run module event 'afterProcess'
tw_event('afterProcess');

// End of processing
