'use strict';
angular.module('gwf4').
controller('ConnectCtrl', function($scope, WebsocketSrvc, ErrorSrvc) {

	$scope.data = {
		state: {
			bool: false,
			text: 'down',
		}	
	};
	
	$scope.connect = function($event) {
		console.log('ConnectCtrl.connect()', $event);
		WebsocketSrvc.connect().then(function(){
			$scope.data.bool = true;
			$scope.data.text = 'established';
		});
	}

	$scope.disconnect = function($event) {
		console.log('ConnectCtrl.disconnect()', $event);
		WebsocketSrvc.disconnect(true);
	};

	$scope.$on('gws-ws-close', function($event) {
		console.log('ConnectCtrl.$on-gws-ws-close()', $event);
		
		
	});
});
