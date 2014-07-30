var alertService = new AlertService();
var crontabService = new CrontabService(alertService);

// Enable tooltips
$(function() {
	$("[data-toggle='tooltip']").tooltip();
});