'use strict';
angular.module('gwf4').
service('CommandSrvc', function(ErrorSrvc) {
	
	var CommandSrvc = this;
	
	CommandSrvc.ERR = function(message)
	{
		return ErrorSrvc.showError(message, "User Error");
	}
	
	CommandSrvc.PONG = function(payload) {
		console.log('CommandSrvc.PONG()', payload);
	};
	
	return CommandSrvc;
});
