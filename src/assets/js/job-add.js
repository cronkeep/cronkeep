var AddJobDialog = function() {
	var form = $('#job-add');
	var timeRadio = $('input[name="time"]', form);
	
	var toggleTimeInputs = function() {
		timeRadio.each(function(i, radio) {			
			var radioChecked = $(radio).prop('checked');
			var timeInputDisabled = !radioChecked;
			
			var radioContainer = $(this).parents('.radio-time');
			$('.input-hour, .input-minute', radioContainer).prop('disabled', timeInputDisabled);
		});		
	};
	
	$('body').on('click', '.repeat', function() {
		var selectedRepeat = $(this).val();
		$.each(['weekly', 'monthly', 'yearly'], function (i, repeat) {
			if (repeat === selectedRepeat) {
				$('.row-customize-' + repeat).addClass('show').removeClass('hidden');
			} else {
				$('.row-customize-' + repeat).addClass('hidden').removeClass('show');
			}
		});
	});
	
	// Toggle accompanying hour and input fields
	timeRadio.on('change', function() {
		toggleTimeInputs();
	});
	
	// Emulate "label" behavior when text acting as label is clicked
	$('.radio-every-hour, .radio-every-minute').click(function() {
		$('input[type="radio"]', $(this)).prop('checked', true);
		toggleTimeInputs();
	});
};