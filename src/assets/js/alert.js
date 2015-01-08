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

var AlertService = function(container) {
    var self = this;
    
    this.pushSuccess = function(message) {
        this.pushMessage(message, 'alert-success');
        return this;
    };
    
    this.pushError = function(message) {
        this.pushMessage(message, 'alert-danger');
        return this;
    };
    
    this.pushMessage = function(message, alertClass) {
        if ($.isArray(message)) {
            message = message.join('<br>');
        }
        
        var alert = $('<div class="alert ' + alertClass + ' alert-dismissible" role="alert">'
            + '<button type="button" class="close" data-dismiss="alert">'
                + '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>'
            + '</button>'
            + message
            + '</div>');
    
        container.empty().append(alert);
        return this;
    };
    
    // "Keep reading" button used for alerts with large amounts of text
    $('.alert a.keep-reading').click(function() {
        var alert = $(this).parents('.alert').first();
        $(this).removeClass('visible-xs-inline').hide();
        $('span.hidden-xs', alert).removeClass('hidden-xs').addClass('visible');
        $('p.hidden-xs', alert).removeClass('hidden-xs').addClass('visible');
    });
};
