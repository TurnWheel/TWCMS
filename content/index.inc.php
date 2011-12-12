<?php
$_t['header'] = 'This is your home page!';

ob_start();
?>

<h3>Use this page as your main home!</h3>

<hr />

<div>
	<h2>Latest News</h2>
	<?php
	// Sample of blog teaser include
	//include 'news/teaser.php';
	?>
	<div class="box">
		<p>Blog stuff can go here, or just static updates if needed</p>
	</div>
</div>

<?php
$_t['content'] = ob_get_contents();
ob_end_clean();
?>
