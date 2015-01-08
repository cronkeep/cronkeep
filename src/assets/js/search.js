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

            // Put regexps together
            var regexAll = new RegExp('(' + words.join('.*?') + ')', 'ig');
            var regexPartial = new RegExp('(' + words.join('|') + ')', 'ig');
            
            // Hide unmatched jobs and highlight search terms
            tableRows.each(function() {
                var tableRow = $(this);
                var matchedAllWords = false;
                
                // Prepare RegExp objects for reuse (reset lastIndex)
                regexAll.lastIndex = 0;
                regexPartial.lastIndex = 0;
                
                var searchableText = $('td[data-searchable=1]', tableRow).map(function() {
                    return $.trim($(this).text());
                }).get().join(' ');
                
                // All words should be found looking across table columns
                if (regexAll.test(searchableText)) {
                    matchedAllWords = true;
                }
                
                $('td[data-searchable=1]', tableRow).each(function(i, element) {
                    // Reset highlighting
                    $(element).highlightRegex(undefined, highlightOptions);
                    
                    // Highlight those words found in current table column
                    if (matchedAllWords && regexPartial.test($(element).text())) {
                        // Highlight matched text
                        $(element).highlightRegex(regexPartial, highlightOptions);
                        matchedTableRows++;
                    }
                });
                
                if (matchedAllWords) {
                    tableRow.show();
                } else {
                    tableRow.hide();
                }
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
    
    // Brings back all the table rows and disables any highlights
    this.clear = function() {
        // Turn off highlights
        $('.table-crontab td[data-searchable=1]').each(function(i, element) {
            $(element).highlightRegex(undefined, highlightOptions);
        });
        
        // Show all table rows
        tableRows.show();
        
        return this;
    };
    
    // Hides empty search notice, brings back all table rows and clears the search field
    // (more powerful than .clear)
    this.reset = function() {
        self.hideEmptySearchNotice();
        self.clear();
        $('.job-search').val('');
    }
    
    // Shows a special container with empty search instructions
    this.showEmptySearchNotice = function() {
        emptySearch.removeClass('hidden');
        table.hide();
        
        return this;
    };
    
    // Hides special container shown for empty searches
    this.hideEmptySearchNotice = function() {
        emptySearch.addClass('hidden');
        table.show();
        
        return this;
    };
    
    // Loads table rows
    var init = function() {
        tableRows = $('tbody tr', table);
        
        return this;
    };
    
    // Assign handlers
    $('body').on('keyup', '.job-search', function(e) {
        self.search($(this).val(), function() {
            // Empty search handler
            emptySearch.removeClass('hidden');
        });
        
        // Reset search when Esc is pressed
        if (e.keyCode == 27) {
            self.reset();
        }
    });
    
    // Reset search when the clear button that some browsers show is clicked
    $('.job-search').on('input', function() {
        if ($(this).val() == '') {
            self.reset();
        }
    });
    
    $('body').on('click', '.job-search-reset', function() {
        self.reset();
    });
    
    $(document).on('jobAdd', function() {
        init();
        self.reset();
    });
};
