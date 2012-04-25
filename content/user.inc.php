<?php
/*
 * TWCMS <Module>
 *
 * Part of User Module
 */

if (!ISUSER) return p_showerror(403);

$T['title'] = $T['header'] = 'Manage Your Account';

$T['content'] = '
<div class="box">
	<p>
		Use these tools to manage and update your account.
		If you have any questions about your membership, please
		<a href="/contact">contact us</a>.
	</p>
</div>

<h3>Account Options</h3>
<ul>
	<li>
		<a href="/user/profile">Update User Profile &amp; Change Password</a>:
		Allows you to change your password, and update your personal profile.
	</li>
</ul>';

// EOF
