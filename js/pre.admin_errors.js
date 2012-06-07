/*
 * TWCMS <Module>
 *
 * JS for admin/errors
 * Part of TWCMS Error Module
 *
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
