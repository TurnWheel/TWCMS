<?php
/*
 * TWCMS 0.9-Beta
 * 
 * This file processes page headers
 * and then searches content directory for the appropriate page.
 * Returns variables such as header, title, content, breadcrumbs, etc.
 * The template is responsible for parsing and displaying these variables.
 */

// Make sure something is set
// Requires that there be a 0 index setting
// 0 index is handled with special case, and can not be ommited!
if (empty($headers) || !isset($headers[0])) $headers = array('a');

/*
 * Get Header Variables
 * URL Format defined in .htaccess
 * <a>/<b>/<c>/<d>
 * Maps to content file: CPATH/<a>_(<b>_(<c>_(<d>))).(html|inc.php)
 */
foreach ($headers AS $num => $key) {
	// Handle first param with special condition (defaults to index)
	if ($num === 0) {
		$_GET[$key] = isset($_GET[$key]) && !empty($_GET[$key])
					? path_escape($_GET[$key]) : 'index';
	}
	else {
		$_GET[$key] = isset($_GET[$key]) ? path_escape($_GET[$key]) : '';
	}
}

// Find current URL
$currurl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

// Remove any query strings/anchors from the url
$surl = parse_url($currurl);
$currurl = $surl['path'] !== $currurl ? $surl['path'] : $currurl;

$rootpage = ''; // Main page (first root in the chain)

// Dynamically adds all roots to array
$pages = array(); // Array of active roots
foreach ($headers AS $num => $key) {
	if ($num === 0) $rootpage = $_GET[$key]; // Set root page (first name found)
	if ($_GET[$key] !== '') $pages[] = $_GET[$key]; // Page array
}

// Full page string using _ format (used for file lookup)
$page = implode('_',$pages);

// Number of sub-pages requested (nesting #)
$numpages = sizeof($pages);

// For tracking breadcrumbs (empty on index; format <title> => $url)
$bcrumbs = array();

// Generate starting bread crumbs if not on index
if ($rootpage !== 'index') {
	$bcrumbs = array('Home' => FULLURL);

	// Add breadcrumbs for current page + subpages
	$prev = '';
	foreach ($pages AS $bcpage) {
		// Make titles look nice (space and captialize)
		// Use $prev to track previous url's
		$bcrumbs[root_url2name($bcpage)] = $prev = $prev.'/'.$bcpage;
	}
}

/** Start file tracking **/
$e404 = FALSE; // Bool to determine if a 404 error needs to be thrown
$php = FALSE; // Determines if include file is PHP or HTML
$file = CPATH.$page; // File name

// This checks for a .html file in CPATH;
// If .html is not found, it looks for .inc.php
$notFound = TRUE;
while ($notFound) {
	// Makes sure that file exists AND is readable
	if (is_readable($file.'.html')) {
		$file .= '.html';
		$notFound = FALSE;
	}
	elseif (is_readable($file.'.inc.php')) {
		$file .= '.inc.php';
		$php = TRUE;
		$notFound = FALSE;
	}

	// If still not found, try to load parent page
	if ($notFound) {
		// Remove last ending to find parent file
		$split = explode('_',$file);
		$sizeof = sizeof($split);

		// Break out of loop, file still not found
		if ($sizeof === 1) break;

		// Create new file string
		array_pop($split);
		$file = implode('_',$split);
	}
}

// Set 404 error if never found
if ($notFound) $e404 = TRUE;

// Load Content if not PHP and no 404
if (!$e404 && !$php) $content = file_get_contents($file);

// Index page? (Custom template tweaks for index page)
$isindex = $page === 'index' || $page === 'indexnew';

// Setup for 404 page
if ($e404) {
    header('HTTP/1.1 404 Not Found');
    $file = CPATH.'error.404.html';
    $title = '- Error: Page Not Found';
    $php = FALSE;

    $bcrumbs = array('Home' => '/','Error: Page Not Found' => $currurl);

    // If we can't use the 404 page, it's not good. Kill the script.
    if (!file_exists($file) || !is_readable($file)) {
		print 'Error 404 Times TWO: A 404 error occured, then the 404'.
			'document could not be found. Please contact the administrator!';
        exit;
    }
}

if ($php) {
	/*
	 * Set the variables allowed by scripts
	 * These 3 variables should be over-ridden in the .inc.php scripts
	 * Other than that, these scripts can do anything.
	 * Including requesting authentication, accepting post data, etc.
	 * This simply locks you into the default layout provided
	 */
	$header = ''; // Sets the h2 tag in template
	$title = ''; // Used for <title> in template (usually same as $header)
	$content = ''; // Body of your page!

	include $file; // Yes, $file is safe

	$header = strip_tags($header); // Strip out HTML from header
}
// Handle .html files
// NOTE: The first line of every .html file becomes the header!
else {
	$content = isset($content) ? $content : '';

	// Split Main Content from Header
	$split = explode("\n", $content, 2);

	// Check to make sure data is valid
	// otherwise use 404 page and post it as a 404 error
	if ($content === '' || empty($split)) {
		header('HTTP/1.1 404 Not Found');
		$data = file_get_contents(CPATH.'error.404.html');
		$split = explode("\n", $data, 2);
	}

	// Split header and content
	if (count($split) > 1) {
		$header = $split[0];
		$content = $split[1];
	}
	else {
		$header = '';
		$content = $data;
	}

	// Strip out HTML from header (tends to sneak in)
	$title = $header = strip_tags($header);

	// Hard code to have no title on index
	if ($isindex) $title = '';
}

// Swap bread crumbs for full title (only if this isnt already set)
if ($header  !== '' && $page !== strtolower($header)) {
	foreach ($bcrumbs AS $name => $url) {
		// Locate pre-set header and remove
		// Comparisions: /page === /page; /page/ === /page/
		// /page/ === /page.'/'; /page === /page/
		if ($url === $currurl || $url === $currurl.'/' ||
				$url === substr($currurl, 0, -1)) {
			unset($bcrumbs[$name]);
		}
	}

	// Set new header for this URL (if not already set)
	if (!isset($bcrumbs[$header])) {
		$bcrumbs[$header] = $currurl;
	}
}

// Add previous pages to title format
// make sure to exclude the current page
// which should already be set
if ($title !== '') {
	$tpages = array(); // Array to hold formatted title pages

	foreach (array_slice(array_reverse($pages),1) AS $val) {
		 // Skip array values to prevent errors and recursion
		if (is_array($val)) continue;

		$tpages[] = root_url2name($val);
	}

	// Add to title (make sure its not empty)
	if (sizeof($tpages) > 0) $title .= ' :: '.implode(' > ',$tpages);
}

// Remove any left-over characters from title
$title = str_replace("\r",'',str_replace("\n",'',trim($title)));

/*
 * Figure out which sidebar to load
 * Possible options:
 * sidebar.<rootpage>.(html|inc.php)
 * sidebar.default.(html|inc.php)
 */
$sb = 'sidebar.'; // Just to make the lines smaller
$sidebar =
	(file_exists(CPATH.$sb.$rootpage.'.inc.php') ? CPATH.$sb.$rootpage.'.inc.php' :
	(file_exists(CPATH.$sb.$rootpage.'.html') ? CPATH.$sb.$rootpage.'.html' :
	(file_exists(CPATH.$sb.'default.inc.php') ? CPATH.$sb.'default.inc.php' :
	(file_exists(CPATH.$sb.'default.html') ? CPATH.$sb.'default.html' : ''))));

/*
 * Simple utility function to get CSS/JS files quickly
 * Filename Format: dir/PREFIX.file.ext?timestamp
 * ****
 * Sample: get_exfile('css','subpage.css');
 * File Name Returned: css/<PREFIX>.subpage.css?_=12343425;
*/
function get_exfile($dir, $name) {
	return file_exists($dir.'/'.PREFIX.'.'.$name) ?
			PREFIX.'.'.$name.'?_='.filemtime($dir.'/'.PREFIX.'.'.$name)
			: FALSE;
}

/* Find CSS files */

// Array of CSS files to load (managed by template)
$cfg['t_css'] = array(get_exfile('css','global.css'));

// Load Sub Page CSS file (if its not index page)
if ($page !== 'index') {
	$cfg['t_css'][] = get_exfile('css','subpage.css');
}

// Load root page CSS file
// if checking parent, or if there is no parent
if ($cfg['res_checkParent'] || $rootpage === $page) {
	$cfg['t_css'][] = get_exfile('css',$rootpage.'.css');
}

// Check for current page CSS
// if current page different from root
if ($rootpage !== $page) {
	$cfg['t_css'][] = get_exfile('css',$page.'.css');
}

/* Find JS Files */

// Array of JS files to load (managed by template)
$cfg['t_js'] = array(get_exfile('js','global.js'));

// Load Sub Page JS file (if its not index page)
if ($page !== 'index') {
	$cfg['t_js'][] = get_exfile('js','subpage.js');
}

// Load root page JS file
// if checking parent, or if there is no parent
if ($cfg['res_checkParent'] || $rootpage === $page) {
	$cfg['t_js'][] = get_exfile('js',$rootpage.'.js');
}

// Check for current page JS
// if current page different from root
if ($rootpage !== $page) {
	$cfg['t_js'][] = get_exfile('js',$page.'.js');
}

/* Recursive Resource Checks
 * (Applies to both css & js)
 * First Make sure environment is sane
 * 1: Check if enabled
 * 2: Check if on a subpage
 * 3: Not if res_checkParent is TRUE && num pages is 2
 */
if ($cfg['res_recursive']) {

	if ($rootpage !== $page && !($cfg['res_checkParent'] && $numpages === 2)) {

		// Check all pages
		$track = array();
		foreach ($pages AS $k => $cp) {
			$track[] = $cp;

			// Skip first (rootpage) and last (currpage)
			if ($k === 0 || $k === ($numpages-1)) continue;

			$curr = implode($track, '_');
			$cfg['t_css'][] = get_exfile('css', $curr.'.css');
			$cfg['t_js'][] = get_exfile('js', $curr.'.js');
		}
	}
}

// End of processing
