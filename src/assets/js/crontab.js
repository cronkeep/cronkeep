$('.job-run').on('click', function() {
	var hash = $(this).attr('data-hash');
	
	$.ajax('/job/run/' + hash).done(function() {
		alert('done');
	});
});