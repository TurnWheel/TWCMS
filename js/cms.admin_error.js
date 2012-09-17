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
