var CrontabService = function() {
	var self = this;
	
	this.runJob = function(job) {
		var hash = job.attr('data-hash');
		
		$.ajax('/job/run/' + hash).done(function(data) {
			alertService.pushSuccess(data.msg);
		}).fail(function(data) {
			alertService.pushError(data.responseJSON.msg);
		});
	};
	
	this.pauseJob = function(job) {
		var hash = job.attr('data-hash');
		
		$.ajax('/job/pause/' + hash).done(function(data) {
			alertService.pushSuccess(data.msg);
			
			var pauseMenuItem  = $('.menu-item-pause', job);
			var resumeMenuItem = $('.menu-item-resume', job);
		
			// Update with the new hash
			job.attr('data-hash', data.hash);
			
			// Refresh interface elements
			job.addClass('inactive');
			pauseMenuItem.addClass('hidden').removeClass('show');
			resumeMenuItem.addClass('show').removeClass('hidden');
		}).fail(function(data) {
			alertService.pushError(data.responseJSON.msg);
		});
	};
	
	this.resumeJob = function(job) {
		var hash = job.attr('data-hash');
		
		$.ajax('/job/resume/' + hash).done(function(data) {
			alertService.pushSuccess(data.msg);
			
			var pauseMenuItem  = $('.menu-item-pause', job);
			var resumeMenuItem = $('.menu-item-resume', job);
		
			// Update with the new hash
			job.attr('data-hash', data.hash);
			
			// Refresh interface elements
			job.removeClass('inactive');
			resumeMenuItem.addClass('hidden').removeClass('show');
			pauseMenuItem.addClass('show').removeClass('hidden');
		}).fail(function(data) {
			alertService.pushError(data.responseJSON.msg);
		});
	};
	
	// Assign handlers
	$('body').on('click', '.job-run', function() {
		var job = $(this).closest('tr');
		self.runJob(job);
	});

	$('body').on('click', '.job-pause', function() {
		var job = $(this).closest('tr');
		self.pauseJob(job);
	});

	$('body').on('click', '.job-resume', function() {
		var job = $(this).closest('tr');
		self.resumeJob(job);
	});
};