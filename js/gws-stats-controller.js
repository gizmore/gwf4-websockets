'use strict';
angular.module('gwf4').
controller('WSStatsCtrl', function($scope, CommandSrvc, WebsocketSrvc) {

	$scope.data = {
		stats: {
			cpu: 0.0,
			memory: 0,
			peak: 0,
			users: 1,
		}
	};

	$scope.refresh = function() {
		console.log('WSStatsCtrl.refresh()');
		WebsocketSrvc.sendCommand('stats', undefined, false).then(function(payload){
			$scope.data.stats = JSON.parse(payload);
		});
	};
	
});
