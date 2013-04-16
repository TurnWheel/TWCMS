/*
 * TWCMS
 * Admin Overview JS
 */

$(function() {
	$('ul li.box').one('click', function() {
		var href = $(this).find('a:first').attr('href');
		window.location = href;
	});
});
