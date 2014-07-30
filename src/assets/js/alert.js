var AlertService = function(content, alertClass) {
	var self = this;
	var alertArea = $('.alert-area');
	
	this.pushSuccess = function(message) {
		this.pushMessage(message, 'alert-success');
		return this;
	};
	
	this.pushError = function(message) {
		this.pushMessage(message, 'alert-danger');
		return this;
	};
	
	this.pushMessage = function(message, alertClass) {
		var alert = $('<div class="alert ' + alertClass + ' alert-dismissible" role="alert">'
			+ '<button type="button" class="close" data-dismiss="alert">'
				+ '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>'
			+ '</button>'
			+ message
			+ '</div>');
	
		alertArea.empty().append(alert);
		return this;
	};
};