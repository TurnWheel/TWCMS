<?php
/*
 * TWCMS <Module>
 *
 * User Module Config
 */

$cfg = array(
	// Enable user login and registration systems
	// requires that sql_enable = TRUE and user table has been created
	'user_enable' => TRUE,

	// Seconds until cookies expire (default 604800, or 1 month)
	'user_expire' => 604800,

	// Enable registration??
	// This allows you to temporarily or permanently
	// disable user registration. Simply blocks access to register page.
	'user_regenable' => TRUE,

	// Notify moderator of new registrations?
	'user_regnotify' => TRUE,

	// Require mod approval on registration?
	// Overrides regnotify setting
	'user_modreg' => TRUE,

	// Send welcome email to new registrations?
	'user_welcome' => FALSE,

	// Email templates and configuration as part of the user system
	// This includes user/mod notifications, and mod approval
	'user_emails' => array(
		// Format of dates in emails
		'date' => 'g:ia T \o\n F j, Y',

		// Email for admin notification
		'regnotify' => array(
			'to' => array('steven@turnwheel.com'),
			'subject' => '[TWCMS] New Registration',
			'headers' => 'From: SomeWebsite<contact@somewebsite.com>',
			'body' => 'A new user has registered an account.

			-User Information-
			{fields}

			More Details: {wwwurl}admin/user/{userid}

			------
			Registration Received @ {date}
			Automated Email Sent By SomeWebsite.com'
		),

		// Email for admin moderation
		'modreg' => array(
			'to' => array('steven@turnwheel.com'),
			'subject' => '[TWCMS] New Member: Approval Required',
			'headers' => 'From: SomeWebsite<contact@somewebsite.com>',
			'body' => 'A new user has requested member access.

			-User Information-
			{fields}

			Visit the admin area below to approve this new user.
			More Details: {wwwurl}admin/user/{userid}

			------
			Registration Received @ {date}
			Automated Email Sent By SomeWebsite.com'
		),

		// Welcome message sent to user
		'welcome' => array(
			'subject' => 'Welcome to TWCMS!',
			'headers' => 'From: SomeWebsite<contact@somewebsite.com>',
			'body' => 'Dear {firstname},

			Welcome to TWCMS, and thank you for joining our website.

			You may now login at to our website by going to:
			{wwwurl}login

			------
			Registration Received @ {date}
			Automated Email Sent By SomeWebsite.com'
		),

		// Account approved
		// only used if modreg is true, this will be sent
		// to the user to notify their account is now active
		// Will only be sent if perm U_NOTIFIED is not set
		'approved' => array(
			'subject' => 'TWCMS Account Approved',
			'headers' => 'Fromt: SomeWebsite<contact@somewebsite.com>',
			'body' => '{firstname} {lastname}-

			Your account for TWCMS has been approved by our moderators.
			You may now login to your account at {wwwurl}login

			Thank you for joining TWCMS.

			------
			Registration Received @ {date}
			Automated Email Sent By SomeWebsite.com'
		),

		// Request to reset password
		'pass_forgot' => array(
			'subject' => '[TWCMS] Request for password reset',
			'headers' => 'From: SomeWebsite<no-reply@somewebsite.com>',
			'body' => 'A request was sent using the Password Recovery Tool
			for this email address. If this request was not sent by you, please
			ignore this email.

			If you are attempting to reset your password, following the link below
			to continue the password reset process.

			{reseturl}

			The above link expires within 24 hours of password reset request.
			If the link has expired, please use our Password Recovery Tool again
			to re-send the request.

			------
			Password Reset Request @ {data}
			Automated Email Sent By SomeWebsite.com'
		),

		// Password has been reset
		'pass_reset' => array(
			'subject' => '[TWCMS] Password reset',
			'headers' => 'From: SomeWebsite<no-reply@somewebsite.com>',
			'body' => 'The password for your TWCMS account has been reset
			using the Password Reset Tool.

			New Password: {password}

			You may now login with the above auto-generated password.
			It is suggested you immeditely change this password once logged in.
			You can change your account profile at: {wwwurl}user/profile

			----
			Password Reset @ {date}
			Automated Email Sent By SomeWebsite.com'
		),
	)
);

/*
 * User Flags (Permissions)
 */

// Basic login privledges
define('U_LOGIN', 1);

// Marked if they have been "approved" and notified
// This is only used if user_modreg is enabled
define('U_NOTIFIED', 2);

// High-level permissions:
// EDITOR is partial access
// ADMIN is full access
define('U_EDITOR', 4);
define('U_ADMIN', 8);

// Default perms for new accounts
define('U_DEFAULT', $cfg['user_modreg'] ? 0 : U_LOGIN);

// Default perms for guests
define('U_GUEST', 0);
