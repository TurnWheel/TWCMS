/*
 * TWCMS <Module>
 *
 * Part of TWCMS Error Module
 *
 * JS for admin/errors
 * Requires /js/jquery.dataTables.min.js
 */

$(function() {
	// Data tables
	$.getScript('/js/jquery.dataTables.min.js', function() {
		$('table.data').dataTable({
			"sPaginationType": "full_numbers"
		});
	});
});


$(function() {
	$('#dump div.array').each(function() {
		var array = $(this);

		var elm = $('<a href="#">Show</a>');
		elm.click(function(e) {
			e.preventDefault();
			var self = $(this);
			var sibling = self.next('div.array');
			var hidden = sibling.is(':hidden');

			if (hidden) {
				sibling.show();
				self.text('Hide');
			}
			else {
				sibling.hide();
				self.text('Show');
			}
		});

		elm.insertBefore(array);
		array.hide();
	});
});
