var CrontabService = function() {
	var self = this;
	var jobToDelete;
	
	this.runJob = function(job) {
		var hash = job.attr('data-hash');
		var runButton = $('.job-run', job);
		runButton.button('loading');
		
		$.ajax('/job/run/' + hash).done(function(data) {
			alertService.pushSuccess(data.msg);
		}).fail(function(data) {
			alertService.pushError(data.responseJSON.msg);
		}).always(function() {
			runButton.button('reset');
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
	
	this.deleteJob = function(job) {
		var hash = job.attr('data-hash');
		
		$.ajax('/job/delete/' + hash).done(function(data) {
			alertService.pushSuccess(data.msg);
			
			// Remove element from the DOM
			job.remove();
		}).fail(function(data) {
			alertService.pushError(data.responseJSON.msg);
		});
	};
	
	// Assign handlers
	$('body').on('click', '.job-add', function() {
		// Prevent button from retaining focus once clicked
		$(this).blur();
		$('#job-add').modal();
	});
	
	$('body').on('click', '.repeat', function() {
		var selectedRepeat = $(this).val();
		$.each(['weekly', 'monthly', 'yearly'], function (i, repeat) {
			if (repeat === selectedRepeat) {
				$('.row-customize-' + repeat).addClass('show').removeClass('hidden');
			} else {
				$('.row-customize-' + repeat).addClass('hidden').removeClass('show');
			}
		});
	});
	
	$('body').on('click', '.job-run', function() {
		var job = $(this).closest('tr');
		self.runJob(job);
		
		// Prevent button from retaining focus once clicked
		$(this).blur();
	});

	$('body').on('click', '.job-pause', function() {
		var job = $(this).closest('tr');
		self.pauseJob(job);
	});

	$('body').on('click', '.job-resume', function() {
		var job = $(this).closest('tr');
		self.resumeJob(job);
	});
	
	$('body').on('click', '.job-confirm-delete', function() {
		jobToDelete = $(this).closest('tr');
		$('#job-delete-confirmation').modal();
	});
	
	$('body').on('click', '.job-delete', function() {
		$('#job-delete-confirmation').modal('hide');
		self.deleteJob(jobToDelete);
	});
};