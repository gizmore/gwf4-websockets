'use strict';
var TGC = angular.module('tgc');
TGC.controller('StatsCtrl', function($rootScope, $scope, CommandSrvc, RequestSrvc) {

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
			
		};
	};

	$scope.refreshStats = function($event) {
		console.log('StatsCtrl.refreshStats()');
	};
	
	$scope.init();

});
