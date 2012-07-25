<?php
/*
 * TWCMS 1.x
 * Template Include
 *
 * This file holds the main template for the whole site
 * Dynamic content files and mods are NOT required to stay
 * within this framework, but it does serve as the launching point.
 */

/*
 * Global include does the initial setup of including configs,
 * loading libraries, and module onLoad calls.
 *
 * This is required for using the base system,
 * and is the only include required for running TWMCS.
 * Content-related features are all in process.inc.php
 */
require 'globals.inc.php';

// Common header variable
$_GET['id'] = isset($_GET['id']) ? intval($_GET['id']) : 0;

/*
 * Header variables to process as part of CMS system
 *
 * This limits the number of nested levels you can go
 * This is also limited by the rewrite rules in .htaccess,
 * where the names of the headers are defined
 *
 * Note: The more you add, the more inefficient the processing will be.
 * Only add as needed.
 */
$headers = array('a','b','c');

/*
 * This is where the real fun begins--
 * Processes headers, determines which content file to load (CPATH),
 * and runs all mod events related to processing.
 *
 * Dynamic content files are permitted to simply exit processing,
 * thereby skipping to rest of this template file, and displaying
 * within their own custom template.
 */
require 'process.inc.php';

// Shortcuts for main template variables
$title = isset($T['title']) ? $T['title'] : '';
$header = isset($T['header']) ? $T['header'] : '';
$content = isset($T['content']) ? $T['content'] : '';
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php print ISINDEX ? 'Home' : $title; ?> :: TWCMS Demo Site</title>

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

	<!--[if IE]>
	<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
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
		<header>
			<div id="logo"><a href="/"><b>Text of Logo</b></a></div>
			<nav id="menu">
				<ul>
					<li><a href="/">Home</a></li>
					<li><a href="/about">About Us</a></li>
					<li><a href="/something">Something Cool</a></li>
					<li><a href="/contact">Contact Us</a></li>
				</ul>
			</nav>
		</header>
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
		<footer>
			<p class="footer_nav">
				<a href="/">Home</a> |
				<a href="/about">About Us</a> |
				<a href="/something">Something Cool</a> |
				<a href="/lame">Lame</a> |
				<a href="/news">Latest News</a> |
				<a href="/contact">Contact Us</a> |
				<a href="/sitemap">Sitemap</a>
			</p>
			<div class="social">
				<a href="#fb" rel="external" class="facebook"><b>Facebook</b></a>
				<a href="#yelp" rel="external" class="yelp"><b>Yelp</b></a>
				<a href="#g" rel="external" class="google"><b>Google Local</b></a>
			</div>
			<p class="copyright">
				&copy; Copyright <?php print date('Y', NOW); ?>
				SomeWebsite.com &mdash; All Rights Reserved<br />

				Website by
				<a href="http://turnwheel.com" rel="external">
					TurnWheel Web Designs
				</a>
			</p>
		</footer>
	</div>

<?php
// Display debug code if enabled
if ($cfg['debug']) {
	t_debug();
	print $T['debug'];
}
?>

</body>
</html>
