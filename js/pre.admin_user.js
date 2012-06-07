/*
 * TWCMS <Module>
 *
 * Part of TWCMS User Module
 *
 * JS for admin/user page
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
