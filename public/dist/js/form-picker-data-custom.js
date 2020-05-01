/*FormPicker Init*/

$(document).ready(function() {
	"use strict";
	
	/* Bootstrap Colorpicker Init*/
	
	
	/* Daterange picker Init*/
	$('.input-daterange-datepicker').daterangepicker({
	  buttonClasses: ['btn', 'btn-sm'],
			applyClass: 'btn-info',
			cancelClass: 'btn-default',
			maxDate: new Date() 
	});
});