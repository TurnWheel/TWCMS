/*
 * Global JS include
 * Coded by Steven Bower (cc) 2011
 * TurnWheel Designs; turnwheel.com
 */

$(function() {
	// Handle external links
	$('a[rel="external"]').click(function() {
		var href = $(this).attr('href');
		
		// Send external link event to Google Analaytics
		try {
			_gaq.push(['_trackEvent','External Links', href.split(/\/+/g)[1], href]);
		} catch (e) {};
		
		window.open(href,'hcv_'+Math.round(Math.random()*11));
		return false;
	});

	// Handle colorbox (rel="colobox" for images)
	$('a[rel="colorbox"]').colorbox();

	// rel="htmlbox" for html pages
	$('a[rel="htmlbox"]').each(function() {
		var self = $(this);
		self.colorbox({
			href: self.data('href') || false,
			scrolling: false,
			innerWidth: '60%'
		});
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
