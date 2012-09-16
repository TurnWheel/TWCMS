<?php
/* User Module Test */
if (ISUSER) {
	print '
	<div>
		<p>Welcome, <strong>'.$U['firstname'].'</strong></p>
		<p>
			<a href="/user/">Manage Account</a> |
			<a href="/logout/">Logout</a>
		</p>
	</div>';
}
else {
	print '
	<div>
		<p>Welcome <strong>Guest</strong></p>
		<p><a href="/login/">Login</a></p>
	</div>';
}
?>

	<h2>Social Networks</h2>
	<div class="social">
		<a href="#facebook" rel="external" class="facebook"><b>Facebook</b></a>
		<a href="#yelp" rel="external" class="yelp"><b>Yelp</b></a>
		<a href="#google" rel="external" class="google"><b>Google Local</b></a>
	</div>

	<h2>Newsletter</h2>
	<p>
		Join our periodic newsletter to stay updated on sales and
		events. <small>Your privacy is important to us, and your
		email address will not be shared.</small>
	</p>

	<form action="#" method="post">
		<input type="email" value="" name="email" required>
		<button type="submit" name="subscribe">Join</button>
	</form>

	<p>&nbsp;</p>

	<h2>DEBUG</h2>
	<p id="debug">Good area for debug code</p>
