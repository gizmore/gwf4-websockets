'use strict';
angular.module('gwf4').
controller('ConnectCtrl', function($scope, WebsocketSrvc, ErrorSrvc, CommandSrvc) {

	$scope.data = {
		state: {
			bool: false,
			text: 'down',
		}	
	};
	
	$scope.connect = function() {
		console.log('ConnectCtrl.connect()');
		WebsocketSrvc.connect(GWF_CONFIG.ws_url);
	}

	$scope.disconnect = function($event) {
		console.log('ConnectCtrl.disconnect()', $event);
		WebsocketSrvc.disconnect(true);
	};
	
	$scope.$on('gws-ws-open', function() {
		$scope.data.state.bool = true;
		$scope.data.state.text = 'established';
		$scope.$apply();
	});

	$scope.$on('gws-ws-close', function($event) {
		console.log('ConnectCtrl.$on-gws-ws-close()', $event);
		$scope.data.state.bool = false;
		$scope.data.state.text = 'down';
		WebsocketSrvc.disconnect(false);
		$scope.$apply();
	});

});
