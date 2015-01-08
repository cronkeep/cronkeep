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

var alertService = new AlertService($('.global-alerts'));
var crontabService = new CrontabService(alertService);
var searchService = new SearchService();

// Allow the "at command is unavailable" alert to be dismissed for a few of days
var alertAtUnavailable = $('.alert-at-unavailable');
if (alertAtUnavailable.length) {
    alertAtUnavailable.on('closed.bs.alert', function() {
        var later = new Date();
        later.setDate(later.getDate() + 3);
        document.cookie = 'showAlertAtUnavailable=0;expires=' + later.toGMTString();
    });
    $('.close', alertAtUnavailable).attr('title', 'Remind me in 3 days').tooltip();
}
