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
		WebsocketSrvc.connect(GWF_CONFIG.ws_url).then(function(){
			$scope.data.bool = true;
			$scope.data.text = 'established';
			CommandSrvc.ping();
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
