<?php
/*
 * TWCMS <Module>
 *
 * Form Module CFG
 */

$cfg = array(
	'contact' => array(
		// Array of field names to process
		'fields' => array('name', 'email', 'message'),

		// Array of required fields
		'required' => array('name', 'email', 'message'),

		// Save received data to DB?
		// Requires 'sql' module to be enabled
		'savedb' => TRUE,

		// Redirect to page after submission?
		// If FALSE, not redirect happens.
		// If a string, it uses string as destination URL
		'redirect' => '/contact/thankyou/',

		// ** Email Configurations **

		// "admin" and "user" are static options
		'emails' => array(
			'admin' => array(
				'enable' => TRUE,

				// Array of emails to send to
				// For admin only. For user is must be a fieldname
				'to' => array('yourname@example.com'),

				// Subject of email
				'subject' => 'Contacted by {name}',

				// Additional email headers (optional)
				// From: and Reply-To: are most common
				// You can enter in {field} to use data here
				// Example: From: {name}<{email}>
				'headers' => 'From: {name}<{email}>',

				// Body of email with {} for var replacements
				// {date} is provided by the library
				// Tabs (\t) are automatically stripped
				'body' => '{name} has contacted you through SomeWebsite.com:

				{message}

				------
				Replies to this email go to {email}
				Message Sent @ {date}
				Automated Email Sent By SomeWebsite.com'
			),
			'user' => array(
				'enable' => FALSE,
				// Field name to grab "to" email from
				'to' => 'email',
				'subject' => 'Thankyou for contacting TWCMS',
				'headers' => 'From: SomeWebsite<contact@somewebsite.com>',
				'body' => 'Thank you for contacting us.

				If your message contained an inquiry, we will try to reply
				to you within 3-4 business days.

				Thank you for your interest in TWCMS

				------
				Message Received @ {date}
				Automated Email Sent By SomeWebsite.com'
			),
		),
	)
);

// EOF
