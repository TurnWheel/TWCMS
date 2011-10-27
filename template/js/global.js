/*
 * Coded by Steven Bower
 * TurnWheel Designs (cc) 2011
 *
 * Global JS Include
 */

$(function() {
	// Tracks External links with Google Analytics
	// Add rel="external" to all links, instead of target="_new"
	$('a[rel="external"]').click(function() {
		var href = $(this).attr('href');

		// Send external link event to Google Analaytics
		try {
			_gaq.push(['_trackEvent', 'External Links', href.split(/\/+/g)[1], href]);
		} catch (e) {};

		window.open(href, 'cms_'+Math.round(Math.random()*11));
		return false;
	});

	// Handle confirmation buttons
	$('button.confirm').click(function() {
		var c = confirm('Are you SURE you want to do this? This action can not be un-done!');
		if (!c) return false;
		else {
			// If confirmed, add "confirm" input element to form
			$(this).after('<input type="hidden" name="confirm" value="true" />');
		}
	});
});
