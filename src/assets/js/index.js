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