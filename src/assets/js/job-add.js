var AddJobDialog = function(startIn) {
	var SPECIFIC_TIME = 'specificTime';
	var EVERY_HOUR = 'everyHour';
	var EVERY_MINUTE = 'everyMinute';
	var WEEKLY = 'weekly';
	var MONTHLY = 'monthly';
	var YEARLY  = 'yearly';
	
	var container = $('#job-add');
	var timePicker = $('input[name="time[picker]"]', container);
	var repeatPicker = $('.repeat', container);
	var simpleForm = $('.job-add-simple-form', container);
	var advancedForm = $('.job-add-advanced-form', container);
	var saveButton = $('.btn-save', container);
	var crontab = $('.table-crontab tbody');
	
	// Retrieves simple or advanced form, whichever is active now
	var getActiveForm = function() {
		if ($('ul.nav li.active a', container).attr('data-mode') === 'simple') {
			return simpleForm;
		}
		return advancedForm;
	};
	
	// Assert-style function to compare time picker's value with passed value
	var assertTime = function(expected) {
		var timePicker = $('input[name="time[picker]"]:checked', container);
		return timePicker.val() === expected;
	};
	
	// Assert-style function to compare repeat picker's value with passed value
	var assertRepeat = function(expected) {
		var repeatPicker = $('input[name="repeat[picker]"]:checked', container);
		return repeatPicker.val() === expected;
	};
	
	var submitHandler = function(form) {
		saveButton.button('loading');
		
		$.post('/job/add', $(form).serialize(), function(data) {
			alertService.pushSuccess(data.msg);
			container.modal('hide');
			form.reset();
			crontab.append(data.html);
			$(document).trigger('jobAdd', {hash: data.hash});
		}).fail(function(data) {
			// @todo Handle server-side errors
		}).always(function() {
			saveButton.button('reset');
		});
	};
	
	var addValidationsAndSubmitHandler = function() {
		$.validator.setDefaults({
			errorClass: 'has-error'
		});
		simpleForm.validate({
			highlight: function(element, errorClass) {
				$(element).parents('.may-host-errors').addClass(errorClass);
			},
			unhighlight: function(element, errorClass) {
				$(element).parents('.may-host-errors').removeClass(errorClass);
			},
			showErrors: function(errorMap, errorList) {
				for (var i in errorList) {
					// Use the same template that Zend\Form\View\Helper\FormElementErrors uses
					var error = '<ul class="error-container"><li>'
							  + errorList[i].message + '</li></ul>';
					var errorHost = $(errorList[i].element).parents('.may-host-errors');
					var errorContainer = $('.error-container', errorHost);
					
					// Replace (server-side) error container or append new one
					if (errorContainer.size()) {
						errorContainer.replaceWith(error);
					} else {
						errorHost.append(error);
					}
				}
			},
			submitHandler: submitHandler,
			rules: {
				'command': {
					required: true
				},
				'time[picker]': {
					required: true
				},
				'time[specificTime][hour]': {
					required: function() {
						return assertTime(SPECIFIC_TIME);
					},
					digits: true,
					range: [0, 23]
				},
				'time[specificTime][minute]': {
					required: function() {
						return assertTime(SPECIFIC_TIME);
					},
					digits: true,
					range: [0, 59]
				},
				'time[everyHour][step]': {
					required: function() {
						return assertTime(EVERY_HOUR);
					},
					digits: true,
					range: [1, 23]
				},
				'time[everyHour][minute]': {
					required: function() {
						return assertTime(EVERY_HOUR);
					},
					digits: true,
					range: [0, 59]
				},
				'time[everyMinute][step]': {
					required: function() {
						return assertTime(EVERY_MINUTE);
					},
					digits: true,
					range: [1, 59]
				},
				'repeat[weekly][dayOfWeek][]': {
					required: function() {
						return assertRepeat(WEEKLY);
					}
				},
				'repeat[monthly][dayOfMonth][]': {
					required: function() {
						return assertRepeat(MONTHLY);
					}
				},
				'repeat[yearly][month]': {
					required: function() {
						return assertRepeat(YEARLY);
					}
				},
				'repeat[yearly][day]': {
					required: function() {
						return assertRepeat(YEARLY);
					},
					digits: true,
					range: [1, 31]
				}
			}
		});
		
		advancedForm.validate({
			submitHandler: submitHandler,
			rules: {
				'command': {
					required: true
				},
				'expression': {
					required: true
				}
			}
		});
	};
	
	// Cycles through time radio options and toggles accompanying inputs
	var toggleTimeInputs = function() {
		timePicker.each(function(i, radio) {			
			var radioChecked = $(radio).prop('checked');
			var timeInputDisabled = !radioChecked;
			
			var radioContainer = $(this).parents('.radio-time');
			$('.input-hour, .input-minute, .input-step', radioContainer)
				.prop('disabled', timeInputDisabled);
		});		
	};
	
	// Shows fieldset corresponding to chosen "Repeat" option and hides the others
	var toggleRepeatFieldsets = function() {
		var selectedRepeat = repeatPicker.val();
		$.each(['weekly', 'monthly', 'yearly'], function (i, repeat) {
			if (repeat === selectedRepeat) {
				$('.fieldset-' + repeat).addClass('show').removeClass('hidden');
			} else {
				$('.fieldset-' + repeat).addClass('hidden').removeClass('show');
			}
		});
	};
	
	// Toggle accompanying inputs when cycling through radio options
	timePicker.on('change', function() {
		toggleTimeInputs();
	});
	
	// Emulate "label" behavior when text acting as label is clicked
	$('.radio-every-hour, .radio-every-minute').click(function() {
		$('input[type="radio"]', $(this)).prop('checked', true);
		toggleTimeInputs();
	});
	
	// Toggle fieldset corresponding to chosen "Repeat" option
	repeatPicker.on('change', function() {
		toggleRepeatFieldsets();
	});
	
	addValidationsAndSubmitHandler();
	toggleTimeInputs();
	toggleRepeatFieldsets();
	
	saveButton.on('click', function(e) {
		var form = getActiveForm();
		form.submit();
	});
};