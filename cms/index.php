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
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title><?php print isset($title) && $title !== '' ? $title : 'Home'; ?> :: Misc. Demo Site</title>

	<meta name="description" content="Enter a descrip" />
	<meta name="keywords" content="Enter keywords" />
	<meta name="robots" content="index, follow" />
	<link rel="shortcut icon" href="/images/fav.png" type="image/x-icon" />
<?php
foreach ($cfg['t_css'] AS $file) {
	if (!is_string($file) || empty($file)) continue;
	print '
	<link rel="stylesheet" type="text/css" href="/css/'.$file.'" media="screen" />';
}
?>


	<script src="/js/jquery.min.js"></script>
	<script src="/js/jquery.colorbox.min.js"></script><?php
foreach ($cfg['t_js'] AS $file) {
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
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
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
				<li><a href="/lame">Lame</a></li>
				<li><a href="/contact">Contact Us</a></li>
			</ul>
		</div>
		<div id="content">
			<div id="content_inner">
			<?php
			// Display bread crumbs
			if (!empty($bcrumbs)) {
				print '
				<div id="breadcrumbs">'."\n";

				foreach ($bcrumbs AS $name => $url) {
					// Handle 'error' name in special case to only show 'Error'
					if (strpos($url,'error.') !== FALSE) {
						$name = 'error';
						$currurl = $url;
					}

					// Printable name
					$linkname = root_url2name($name);

					// Display link if it matches current URL (checks for trailing slash as well)
					if ($url !== $currurl && $url.'/' !== $currurl) {
						print '<a href="'.$url.'">'.$linkname.'</a> &gt; ';
					}
					else print '<strong>'.$linkname.'</strong>';
				}

				print '
				</div>'."\n";
			}

			// Print out header and content variables
			if (isset($header) && $header !== '') {
				print '<h2><a href="'.$currurl.'">'.$header.'</a></h2>';
			}
			print '<div>'.$content.'</div>'."\n";
			?>
			</div>
			<!-- End content_inner -->

			<!-- Begin content_side -->
			<div id="content_side">
			<?php
			// Sidebar
			if ($sidebar !== '') include $sidebar;
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
print '<!-- Time: '.(microtime(TRUE)-$_starttime).'s -->';
print '<!-- SQL #: '.$cfg['sql']['count'].' -->';
?>
</body>
</html>
