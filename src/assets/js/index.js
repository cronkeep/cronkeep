var alertService = new AlertService();
var crontabService = new CrontabService(alertService);
var searchService = new SearchService();
var addJobDialog = new AddJobDialog();

// Enable tooltips
$(function() {
	$("[data-toggle='tooltip']").tooltip();
});

//$('#job-add').modal();