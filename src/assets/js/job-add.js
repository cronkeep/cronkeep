/**
 * Copyright 2014 Bogdan Ghervan
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

var AddJobDialog = function(container, crontabService, globalAlertService) {
    var SPECIFIC_TIME = 'specificTime';
    var EVERY_HOUR = 'everyHour';
    var EVERY_MINUTE = 'everyMinute';
    var WEEKLY = 'weekly';
    var MONTHLY = 'monthly';
    var YEARLY  = 'yearly';
    
    var timePicker = $('input[name="time[picker]"]', container);
    var repeatPicker = $('.repeat', container);
    var simpleForm = $('.job-add-simple-form', container);
    var advancedForm = $('.job-add-advanced-form', container);
    var saveButton = $('.btn-save', container);
    
    var simpleFormAlertService = new AlertService($('.form-alerts', simpleForm));
    var advancedFormAlertService = new AlertService($('.form-alerts', advancedForm));
    
    var errorClass = 'has-error';
    
    // Opens job add / edit dialog
    this.open = function() {
        container.modal();
    };
    
    // Tells whether the simple form is the one currently active
    var isSimpleFormActive = function() {
        return $('ul.nav li.active a', container).attr('data-mode') === 'simple';
    };
    
    // Retrieves simple or advanced form, whichever is active now
    var getActiveForm = function() {
        if (isSimpleFormActive()) {
            return simpleForm;
        }
        return advancedForm;
    };
    
    // Retrieves alert service for the currently active form
    var getFormAlertService = function() {
        if (isSimpleFormActive()) {
            return simpleFormAlertService;
        }
        return advancedFormAlertService;
    };
    
    // Assert-style function to compare time picker's value with passed value
    var assertTime = function(expected) {
        var timePicker = $('input[name="time[picker]"]:checked', container);
        return timePicker.val() === expected;
    };
    
    // Assert-style function to compare repeat picker's value with passed value
    var assertRepeat = function(expected) {
        var repeatPicker = $('select[name="repeat[picker]"]', container);
        return repeatPicker.val() === expected;
    };
    
    var submitHandler = function(form) {
        saveButton.button('loading');
        var oldHash = $('input[name=hash]', form).val();
        stripNewlines($('textarea.command', form));
        
        $.post('/job/save', $(form).serialize(), function(data) {            
            // Show success message, close dialog and reset form
            globalAlertService.pushSuccess(data.msg);
            container.modal('hide');
            form.reset();
            
            // Refresh edited job in the grid
            if (oldHash) {
                crontabService.updateJob(oldHash, data.html, data.hash);
            
            // Append new job to the grid
            } else {
                crontabService.appendJob(data.html, data.hash);
            }
        }).fail(function(data) {
            var formAlertService = getFormAlertService();
            formAlertService.pushError(data.responseJSON.msg);
        }).always(function() {
            saveButton.button('reset');
        });
    };
    
    // Determines closest parent that can receive the errorClass
    var getParentToHighlight = function(element) {
        return $(element).closest('[data-has-error~="' + $(element).attr('name') + '"]');
    }
    
    // Determines error container for the given element
    // (by looking for a matching data-error attribute)
    var getErrorContainer = function(element) {
        return $('[data-error="' + $(element).attr('name') + '"]', container);
    };
    
    // Displays given error (while replacing any existing error)
    var addError = function(element, errorMessage) {
        var errorContainer = getErrorContainer(element);

        errorContainer.empty()
            .append('<li>' + errorMessage + '</li>')
            .attr('data-source', 'client');
        
        return this;
    }
    
    // Tells whether element has any errors attached to it
    var hasError = function(element) {
        var errorContainer = getErrorContainer(element);
        return errorContainer.children().size() > 0;
    }
    
    // Removes error container for given element
    var removeError = function(element) {
        var errorContainer = getErrorContainer(element);
        errorContainer.empty();
    }
    
    var addValidationsAndSubmitHandler = function() {
        $.validator.setDefaults({
            errorClass: errorClass,
            highlight: function(element, errorClass) {
                getParentToHighlight(element).addClass(errorClass);
            },
            unhighlight: function(element, errorClass) {
                var parent = getParentToHighlight(element);
                var relElementNames = parent.attr('data-has-error').split(' ');
                
                // A parent can receive errorClass for several elements, listed in data-has-error.
                // Make sure all elements are error-free before unhighlighting.
                var doUnhighlight = true;
                for (var i in relElementNames) {
                    if (hasError($('[name="' + relElementNames[i] + '"]'))) {
                        doUnhighlight = false;
                        break;
                    }
                }
                
                if (doUnhighlight) {
                    parent.removeClass(errorClass);
                }
            },
            showErrors: function(errorMap, errorList) {
                var i, elements, error;

                // Highlight erroneous elements and attach errors
                for (i = 0; error = errorList[i]; i++) {
                    this.settings.highlight.call(this, error.element, this.settings.errorClass);
                    addError(error.element, error.message);
                }

                // Unhighlight valid elements and remove errors
                for (i = 0, elements = this.validElements(); elements[i]; i++) {
                    removeError(elements[i]);
                    this.settings.unhighlight.call(this, elements[i], this.settings.errorClass);
                }
            }
        });
        
        simpleForm.validate({
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
                'repeat[yearly][dayOfMonth]': {
                    required: function() {
                        return assertRepeat(YEARLY);
                    },
                    digits: true,
                    range: [1, 31]
                }
            },
            messages: {
                'command': {
                    required: 'Command is required'
                },
                'time[specificTime][hour]': {
                    required: 'Hour is required',
                    digits: 'Hour should be a valid number',
                },
                'time[specificTime][minute]': {
                    required: 'Minute is required',
                    digits: 'Minute should be a valid number'
                },
                'time[everyHour][step]': {
                    required: 'Frequency is required',
                    digits: 'Frequency should be a valid number'
                },
                'time[everyHour][minute]': {
                    required: 'Minute is required',
                    digits: 'Minute should be a valid number'
                },
                'time[everyMinute][step]': {
                    required: 'Frequency is required',
                    digits: 'Frequency should be a valid number'
                },
                'repeat[weekly][dayOfWeek][]': {
                    required: 'Day of week is required'
                },
                'repeat[monthly][dayOfMonth][]': {
                    required: 'Day of month is required'
                },
                'repeat[yearly][month]': {
                    required: 'Month is required'
                },
                'repeat[yearly][dayOfMonth]': {
                    required: 'Day of month is required',
                    digits: 'Day of month should be a valid number'
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
            },
            messages: {
                'command': {
                    required: "Command is required"
                },
                'expression': {
                    required: "Time expression is required"
                }
            }
        });
    };
    
    // Cycles through time radio options and toggles accompanying inputs (hour, minute, step)
    var toggleTimeInputs = function() {
        timePicker.each(function(i, radio) {            
            var radioChecked = $(radio).prop('checked');
            var timeInputDisabled = !radioChecked;
            var radioContainer = $(this).parents('.radio-time');
            
            // Disable inputs and remove any validation errors
            $('input[type="number"]', radioContainer).each(function(i, timeInput) {
                $(timeInput).prop('disabled', timeInputDisabled);
                
                if (timeInputDisabled) {
                    resetValidatedInput($(timeInput));
                }
            });
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
    
    // Removes validation artifacts for a disabled input
    // (workaround for jzaefferer/jquery-validation#224)
    var resetValidatedInput = function(input) {
        var parent = getParentToHighlight(input);
        parent.removeClass(errorClass);
        
        removeError(input);
        input.removeAttr('aria-invalid');
    };
    
    // Pads time fields with "0" for single-digit values
    // (works only on IE and Chrome for number inputs)
    var padTimeField = function(field) {
        var val = field.val();
        if (val !== '') {
            field.val(String('00' + val).slice(-2));
        }
        return this;
    };
    
    // Removes any newlines (CR, LF) in the given field
    var stripNewlines = function(field) {
        field.val(field.val().replace(/[\r\n]/g, ''));
        return this;
    };
    
    // Toggle accompanying inputs when cycling through radio options
    timePicker.on('change', toggleTimeInputs);
    
    // Emulate "label" behavior when text acting as label is clicked
    $('.radio-every-hour, .radio-every-minute').click(function() {
        $('input[type="radio"]', $(this)).prop('checked', true);
        toggleTimeInputs();
    });
    
    // Toggle fieldset corresponding to chosen "Repeat" option
    repeatPicker.on('change', function() {
        toggleRepeatFieldsets();
    });
    
    // Restrict the usage of newlines (CR, LF) in the command field
    $('textarea.command', container).on('blur', function() {
        stripNewlines($(this));
    });
    
    addValidationsAndSubmitHandler();
    toggleTimeInputs();
    toggleRepeatFieldsets();
    
    padTimeField($('input[name="time[specificTime][hour]"]', container));
    padTimeField($('input[name="time[specificTime][minute]"]', container));
    padTimeField($('input[name="time[everyHour][minute]"]', container));
    
    saveButton.on('click', function(e) {
        var form = getActiveForm();
        form.submit();
    });
};