var alertService = new AlertService();
var crontabService = new CrontabService(alertService);
var searchService = new SearchService();
var addJobDialog = new AddJobDialog();

// Enable tooltips now and in the future
$(function() {
	$(".table-crontab [data-toggle='tooltip']").tooltip();
});
$(document).on('jobAdd', function(event, data) {
	var job = $(".table-crontab [data-hash='" + data.hash + "']");
	$("[data-toggle='tooltip']", job).tooltip();
});