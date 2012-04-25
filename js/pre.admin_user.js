/*
 * TWCMS <Module>
 *
 * JS for admin/user
 * Part of TWCMS User Module
 *
 * Requires /js/jquery.dataTables.min.js
 */

$(function() {
	// Data tables
	$.getScript('/js/jquery.dataTables.min.js',function() {
		$('table.data').dataTable({
			"sPaginationType": "full_numbers"
		});
	});
});
