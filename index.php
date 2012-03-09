<?php
// Global does the initial setup of including
// config, libraries, and setting up DB connection
require 'globals.inc.php';

// Common header variable
$_GET['id'] = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Header variables to process (add as many as needed)
$headers = array('a','b','c');

// Process headers and get page details
require 'process.inc.php';

// Proccess main template variables
$title = isset($T['title']) ? $T['title'] : '';
$header = isset($T['header']) ? $T['header'] : '';
$content = isset($T['content']) ? $T['content'] : '';

/*
 * Breadcrumb function
 * There isn't a better place to put this currently,
 * but I should figure out something later.
 *
 * Simple: Call with $T['bcrumbs'] array
 * separated by $sep
 */
function t_bcrumbs($bcrumbs, $sep = '&gt;') {
	// If no input, just return empty string
	if (empty($bcrumbs)) return '';

	// Set current url to VAR for purpose of this function
	$currurl = CURRURL;

	$ret = '
	<div id="breadcrumbs">'."\n";

	foreach ($bcrumbs AS $name => $url) {
		// Printable name
		$linkname = root_url2name($name);

		// Display link if does not match current URL
		// (checks for trailing slash as well)
		if ($url !== $currurl && $url.'/' !== $currurl) {
			$ret .= '<a href="'.$url.'">'.$linkname.'</a> &gt; ';
		}
		// Display current url as bold
		else $ret .= '<strong>'.$linkname.'</strong>';
	}

	$ret .= '
	</div>'."\n";

	return $ret;
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>
	<?php print $title === '' ? 'Home' : $title; ?>
	:: Misc. Demo Site
	</title>

	<meta name="description" content="Enter a descrip" />
	<meta name="keywords" content="Enter keywords" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="/images/fav.png" type="image/x-icon" />
<?php
foreach ($T['css'] AS $file) {
	if (!is_string($file) || empty($file)) continue;
	print '
	<link rel="stylesheet" type="text/css" href="/css/'.$file.'" media="screen" />';
}
?>


	<script src="/js/jquery.min.js"></script>
	<script src="/js/jquery.colorbox.min.js"></script><?php
foreach ($T['js'] AS $file) {
	if (!is_string($file) || empty($file)) continue;
	print '
	<script src="/js/'.$file.'"></script>';
}
?>

	<script>
	//<!--
	var _gaq = _gaq || [];
	//_gaq.push(['_setAccount', 'UA-92928-<#>']);
	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script');
		ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' :
			'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(ga, s);
	})();
	//-->
	</script>
</head>
<body>
	<div id="top">
		<h1><a href="/">Main Title Of Website (Hidden by default)</a></h1>
	</div>
	<div id="container">
		<div id="header">
			<div id="logo"><a href="/"><b>Text of Logo</b></a></div>
			<ul id="menu">
				<li><a href="/">Home</a></li>
				<li><a href="/about">About Us</a></li>
				<li><a href="/something">Something Cool</a></li>
				<li><a href="/contact">Contact Us</a></li>
			</ul>
		</div>
		<div id="content">
			<div id="content_inner">
			<?php
			// Display bread crumbs
			print t_bcrumbs($T['bcrumbs'], '&gt;');

			// Print out header as h2
			if ($header !== '') {
				// Prevents HTML from sneaking into header variable
				$header = strip_tags($header);
				print '<h2><a href="'.CURRURL.'">'.$header.'</a></h2>';
			}

			// Print out content wrapped in a div
			print '<div>'.$content.'</div>'."\n";
			?>
			</div>
			<!-- End content_inner -->

			<!-- Begin content_side -->
			<div id="content_side">
			<?php
			// Include sidebar file
			// yes, $T['sidebar'] is include safe
			if ($T['sidebar'] !== '') include $T['sidebar'];
			?>
			</div>
			<!-- End content_side -->

			<div class="clear"></div>
		</div>
		<!-- End #content -->
	</div>
	<!-- End #container -->
	<div id="gotop"><a href="#top">Return to Top of Page</a></div>
	<div id="footer_top"></div>
	<div id="footer">
		<div id="footer_inner">
			<p>
				<a href="/">Home</a> |
				<a href="/about">About Us</a> |
				<a href="/something">Something Cool</a> |
				<a href="/lame">Lame</a> |
				<a href="/news">Latest News</a> |
				<a href="/contact">Contact Us</a> |
				<a href="/sitemap">Sitemap</a>
			</p>
			<div class="social">
				<a href="#facebook" rel="external" class="facebook"><b>Facebook</b></a>
				<a href="#yelp" rel="external" class="yelp"><b>Yelp</b></a>
				<a href="#google" rel="external" class="google"><b>Google Local</b></a>
			</div>
			<p class="copyright">
				&copy; Copyright <?php print date('Y', NOW); ?>
				SomeWebsite.com &mdash; All Rights Reserved<br />

				Website by <a href="http://turnwheel.com" rel="external">TurnWheel Web Designs</a>
			</p>
		</div>
	</div>
<?php
if ($cfg['debug']) {
	print '<!-- Time: '.(microtime(TRUE)-$_starttime).'s -->';
	if ($cfg['db_enable']) {
		print "\n".'<!-- SQL #: '.$cfg['sql']['count'].' -->';
	}
}
?>

</body>
</html>
