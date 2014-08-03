var alertService = new AlertService();
var crontabService = new CrontabService(alertService);
var searchService = new SearchService();

// Enable tooltips
$(function() {
	$("[data-toggle='tooltip']").tooltip();
});