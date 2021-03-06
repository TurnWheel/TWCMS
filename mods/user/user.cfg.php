<?php
/*
 * TWCMS <Module>
 *
 * User Module Config
 */

$cfg = array(
	// Seconds until cookies expire (default 604800, or 1 month)
	'expire' => 604800,

	// Enable registration??
	// This allows you to temporarily or permanently
	// disable user registration. Simply blocks access to register page.
	'regenable' => TRUE,

	// Notify moderator of new registrations?
	'regnotify' => TRUE,

	// Require mod approval on registration?
	// Overrides regnotify setting
	'modreg' => TRUE,

	// Send welcome email to new registrations?
	'welcome' => FALSE,

	// Email templates and configuration as part of the user system
	// This includes user/mod notifications, and mod approval
	'emails' => array(
		// Format of dates in emails
		'date' => 'g:ia T \o\n F j, Y',

		// Email for admin notification
		'regnotify' => array(
			'to' => array('steven@turnwheel.com'),
			'subject' => '[{sitename}] New Registration',
			'headers' => 'From: {sitename}<contact@{domain}>',
			'body' => 'A new user has registered an account.

			-User Information-
			{fields}

			More Details: {wwwurl}admin/user/{userid}

			------
			Registration Received @ {date}
			Automated Email Sent By {domain}'
		),

		// Email for admin moderation
		'modreg' => array(
			'to' => array('steven@turnwheel.com'),
			'subject' => '[{sitename}] New Member: Approval Required',
			'headers' => 'From: {sitename}<contact@{domain}>',
			'body' => 'A new user has requested member access.

			-User Information-
			{fields}

			Visit the admin area below to approve this new user.
			More Details: {wwwurl}admin/user/{userid}

			------
			Registration Received @ {date}
			Automated Email Sent By {domain}'
		),

		// Welcome message sent to user
		'welcome' => array(
			'subject' => 'Welcome to {sitename}!',
			'headers' => 'From: {sitename}<contact@{domain}>',
			'body' => 'Dear {firstname},

			Welcome to {sitename}, and thank you for joining our website.

			You may now login at to our website by going to:
			{wwwurl}login

			------
			Registration Received @ {date}
			Automated Email Sent By {domain}'
		),

		// Account approved
		// only used if modreg is true, this will be sent
		// to the user to notify their account is now active
		// Will only be sent if perm U_NOTIFIED is not set
		'approved' => array(
			'subject' => '{sitename} Account Approved',
			'headers' => 'From: {sitename}<contact@{domain}>',
			'body' => '{firstname} {lastname}-

			Your account for {sitename} has been approved by our moderators.
			You may now login to your account at {wwwurl}login

			Thank you for joining {sitename}.

			------
			Registration Received @ {date}
			Automated Email Sent By {domain}'
		),

		// Request to reset passphrase
		'pass_forgot' => array(
			'subject' => '[{sitename}] Request for passphrase reset',
			'headers' => 'From: {sitename}<no-reply@{domain}>',
			'body' => 'A request was sent using the Passphrase Recovery Tool
			for this email address. If this request was not sent by you, please
			ignore this email.

			If you are attempting to reset your passphrase, following the link below
			to continue the passphrase reset process.

			{reseturl}

			The above link expires within 24 hours of passphrase reset request.
			If the link has expired, please use our Passphrase Recovery Tool again
			to re-send the request.

			------
			Passphrase Reset Request @ {date}
			Automated Email Sent By {domain}'
		),

		// Passphrase has been reset
		'pass_reset' => array(
			'subject' => '[{sitename}] Passphrase reset',
			'headers' => 'From: {sitename}<no-reply@{domain}>',
			'body' => 'The passphrase for your {sitename} account has been reset
			using the Passphrase Reset Tool.

			New Passphrase: {passphrase}

			You may now login with the above auto-generated passphrase.
			It is suggested you immeditely change this passphrase once logged in.
			You can change your account profile at: {wwwurl}user/profile

			----
			Passphrase Reset @ {date}
			Automated Email Sent By {domain}'
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
// STAFF is partial access
// ADMIN is full access
define('U_STAFF', 4);
define('U_ADMIN', 8);

// "Super" gives access to higher-level CMS data
// such as raw error reports
define('U_SUPER', 128);

// Text representation of permissions
$cfg['flags'] = array(
	U_LOGIN => 'Enabled',
	U_NOTIFIED => 'Approved',
	U_STAFF => 'Staff',
	U_ADMIN => 'Admin Area'
);

// Default perms for new accounts
define('U_DEFAULT', $cfg['modreg'] ? 0 : U_LOGIN);

// Default perms for guests
define('U_GUEST', 0);
