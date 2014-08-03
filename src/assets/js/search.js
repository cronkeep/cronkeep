var SearchService = function(searchData) {
	var self = this;
	var table = $('.table-crontab');
	var tableRows = $('tbody tr', table);
	var emptySearch = $('.empty-search');
	var highlightOptions = {
		tagType: 'mark',
		className: 'mark'
	};
	
	this.search = function(phrase) {
		// Split search phrase into words
		var words = phrase.match(/\S+/g);
		var matchedTableRows = 0;
		
		if (words) {
			// Escape words to be treated as literal strings within a regex; inpired by:
			// https://developer.mozilla.org/en/docs/Web/JavaScript/Guide/Regular_Expressions
			for (var i in words) {
				words[i] = words[i].replace(/([.*+?^${}()|\[\]\/\\])/g, "\\$1");
			}

			// Put together a regexp
			var regex = new RegExp('(' + words.join('.*?') + ')', 'ig');

			// Hide unmatched jobs and highlight search terms
			tableRows.each(function() {
				var tableRow = $(this);
				
				$('td[data-searchable=1]', tableRow).each(function(i, element) {
					$(element).highlightRegex(undefined, highlightOptions);
					
					if (regex.test($(element).text())) {
						// Highlight matched text
						$(element).highlightRegex(regex, highlightOptions);
						tableRow.show();
						matchedTableRows++;
					} else {
						tableRow.hide();
					}
				});
			});
			
			if (matchedTableRows) {
				self.hideEmptySearchNotice();
			} else {
				self.showEmptySearchNotice();
			}
		} else {
			self.clear();
		}
		
		return this;
	};
	
	this.clear = function() {
		// Turn off highlights
		$('.table-crontab td[data-searchable=1]').each(function(i, element) {
			$(element).highlightRegex(undefined, highlightOptions);
		});

		// Show all table rows
		tableRows.show();
		
		return this;
	};
	
	this.showEmptySearchNotice = function() {
		emptySearch.removeClass('hidden');
		table.hide();
		
		return this;
	};
	
	this.hideEmptySearchNotice = function() {
		emptySearch.addClass('hidden');
		table.show();
		
		return this;
	};
	
	// Assign handlers
	$('body').on('keyup', '.job-search', function() {
		self.search($(this).val(), function() {
			// Empty search handler
			emptySearch.removeClass('hidden');
		});
	});
	
	$('body').on('click', '.job-search-reset', function() {
		self.hideEmptySearchNotice();
		self.clear();
		$('.job-search').val('');
	});
};