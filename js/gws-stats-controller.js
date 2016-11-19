'use strict';
angular.module('gwf4').
controller('StatsCtrl', function($scope, CommandSrvc, WebsocketSrvc) {

	$scope.data = {
		stats: {
			cpu: 0.0,
			memory: 0,
			peak: 0,
			players: 1,
		}
	};
	
	$scope.init = function() {
		CommandSrvc.stats = function($scope) {
			WebsocketSrvc.sendCommand('stats');
		};
		CommandSrvc.STATS = function($scope, payload) {
			console.log('CommandSrvc.STATS()', payload);
			$scope.data.stats = JSON.parse(payload);
		};
	};

	$scope.refreshStats = function($event) {
		console.log('StatsCtrl.refreshStats()');
	};
	
	$scope.init();

});
