jQuery.noConflict();
jQuery(document).ready(function($) {

// Save data.
	$('.metabox_submit').click(function(e) {
		e.preventDefault();
		$('#publish').click();
	});

// Datepicker.
if( $('.pickadate').length > 0 ) {
	var dateToday = new Date();
	var dateOptions = {
		dateFormat: EMOE.dateFormat,
		dayNames: EMOE.daysFull,
		dayNamesMin: EMOE.daysShort,
		monthNames: EMOE.monthsFull,
		monthNamesShort: EMOE.monthsShort,
	 	nextText: EMOE.next,
		prevText: EMOE.previous,
		changeMonth: true,
		changeYear: true,
		yearRange: "0:+4",
		defaultDate: dateToday,
		minDate: '+1d',
	};
	$('.pickadate').datepicker(dateOptions);
}

// Time Picker.
if( $('.pickatime').length > 0 ) {
	var timeOptions = {
		'timeFormat': EMOE.time_format,
		'step' : 30,
		'minTime': '06:00',
		'maxTime': '23:00',
		'forceRoundTime': false,
		'useSelect': false,
	};
	$('.pickatime').timepicker(timeOptions);
}

// Toggle.
	var Toggle 	= $('#_ongoing_check');
	var Table 	= $('#ongoing-events');
	var	Inputs 	= Table.find('input');

	// On Page load.
	if( Toggle.is(':checked') ) {
		Table.show();
		Inputs.attr('required', true);

	} else {
		Table.hide();
		Inputs.attr('required', false);
	}

	// On Toggle.
	Toggle.on('click', function() {
		Table.toggle(500);
		Inputs.attr('required', (Toggle.is(':checked') ? true : false) );

		beginDate = new Date( $('.em-date-input').val() );
		beginDate.setDate(beginDate.getDate() + 1);
		newDate =  $.datepicker.formatDate(EMOE.dateFormat, new Date( beginDate ));
		$('[name="_ongoing_events[2][date]"]').val( newDate );
		$('[name="_ongoing_events[2][start]"]').val( $('#start-time').val() );
		$('[name="_ongoing_events[2][end]"]').val( $('#end-time').val() );

	});

// Add sub event.
	$(document.body).on('click', '.ongoing-add', function() {
		var secondlast 	= $('#ongoing-events tr').last();
		var newClone 	= secondlast.clone();

		newClone.find('input').each(function() {
			var oldName = $(this).attr('name');
			var newName = oldName.replace(/\d+/, function(match) {
				return (parseInt(match)+1);
			});
			$(this).attr('name', newName);
			$(this).val('');
		});

		newClone.find('.count').each(function() {
			var oldName = $(this).html();
			var newName = oldName.replace(/\d+/, function(match) {
				return (parseInt(match)+1);
			});
			$(this).html( newName);
		});

		newClone.find('.pickadate').each(function() {
			$(this).removeAttr('id').removeClass('hasDatepicker').removeAttr('value');
			var $this 	= $(this);
			var $prev 	= secondlast.find('.pickadate').last();

			prevDate 	= $prev.datepicker("getDate");
			prevDate.setDate( prevDate.getDate() + 1);

			nextDate 	= $.datepicker.formatDate(EMOE.dateFormat, new Date( prevDate ));
			$(this).datepicker(dateOptions).val( nextDate );
			$(this).datepicker('option', 'minDate', nextDate );

		});

		newClone.find('.pickatime').each(function() {
			$(this).timepicker(timeOptions);
		});

		newClone.find('.start-time').val( $('#start-time').val() );
		newClone.find('.end-time').val( $('#end-time').val() );

		newClone.find('.edit-link').remove();

		newClone.insertAfter(secondlast);
	});

// Remove sub event.
	$(document.body).on('click', '.ongoing-remove', function() {
    	$(this).closest('tr').remove();
	});

});
(jQuery);
