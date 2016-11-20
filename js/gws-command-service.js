'use strict';
angular.module('gwf4').
service('CommandSrvc', function($rootScope, $injector, ErrorSrvc, WebsocketSrvc) {
	
	var CommandSrvc = this;
	
	/////////////////////
	// Client commands //
	/////////////////////
	CommandSrvc.ping = function($scope, version) {
		return WebsocketSrvc.sendCommand('ping', version);
	};
	
	/////////////////////
	// Server commands //
	/////////////////////
	CommandSrvc.ERR = function($scope, message)
	{
		return ErrorSrvc.showError(message, "User Error");
	}
	
	CommandSrvc.PONG = function($scope, payload) {
		console.log('CommandSrvc.PONG()', payload);
		$scope.data.version = payload;
	};
	
	return CommandSrvc;
});
