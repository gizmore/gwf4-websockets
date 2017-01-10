'use strict';
angular.module('gwf4').
controller('WSStatsCtrl', function($scope, WebsocketSrvc) {

	$scope.data = {
		stats: {
			memory: 0,
			peak: 0,
			users: 1,
		}
	};

	$scope.refresh = function() {
		console.log('WSStatsCtrl.refresh()');
		return WebsocketSrvc.sendBinary(GWS_Message().cmd(0x0101).sync()).then($scope.afterRefresh);
	};
	
	$scope.afterRefresh = function(gwsMessage) {
		console.log('WSStatsCtrl.afterRefresh()', gwsMessage);
		var stats = $scope.data.stats;
		stats.memory = gwsMessage.read32();
		stats.peak = gwsMessage.read32();
		stats.users = gwsMessage.read16();
	};
	
});
