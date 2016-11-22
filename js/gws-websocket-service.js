'use strict';
angular.module('gwf4').
service('WebsocketSrvc', function($q, $rootScope, ErrorSrvc) {
	
	var WebsocketSrvc = this;
	
	WebsocketSrvc.NEXT_MID = 1000000;
	WebsocketSrvc.SYNC_MSGS = {};
	
	WebsocketSrvc.SOCKET = null;
	
//	WebsocketSrvc.QUEUE = [];
//	WebsocketSrvc.QUEUE_INTERVAL = null;
//	WebsocketSrvc.QUEUE_SEND_MILLIS = 250;
	
	////////////////
	// Connection //
	////////////////
	WebsocketSrvc.withConnection = function(url) {
		if (WebsocketSrvc.connected()) {
			var defer = $q.defer();
			defer.resolve();
			return defer.promise;
		}
		return WebsocketSrvc.connect(url);
	}

	WebsocketSrvc.connect = function(url) {
		console.log('WebsocketSrvc.connect()', url);
		var defer = $q.defer();
		if (WebsocketSrvc.SOCKET == null) {
			var ws = WebsocketSrvc.SOCKET = new WebSocket(url);
			ws.onopen = function() {
				WebsocketSrvc.startQueue();
		    	defer.resolve();
		    	$rootScope.$broadcast('gws-ws-open');
			};
		    ws.onclose = function() {
		    	$rootScope.$broadcast('gws-ws-close');
		    	WebsocketSrvc.disconnect(false);
		    };
		    ws.onerror = function(error) {
		    	WebsocketSrvc.disconnect(false);
				defer.reject(error);
		    };
		    ws.onmessage = function(message) {
		    	if (message.data.indexOf('ERR:') === 0) {
		    		ErrorSrvc.showError(message.data, 'User Error');
		    	}
		    	else if (message.data.indexOf(':MID:') >= 0) {
		    		if (!WebsocketSrvc.syncMessage(message.data)) {
		    			$rootScope.$broadcast('gws-ws-message', message);
		    		}
		    	} else {
	    			$rootScope.$broadcast('gws-ws-message', message);
		    	}
		    };
		}
		else {
			defer.reject();
		}
		return defer.promise;
	};
	
	WebsocketSrvc.disconnect = function(event) {
		console.log('WebsocketSrvc.disconnect()');
		if (WebsocketSrvc.SOCKET != null) {
			WebsocketSrvc.SOCKET.close();
			WebsocketSrvc.SOCKET = null;
			WebsocketSrvc.NEXT_MID = 1000000;
			WebsocketSrvc.SYNC_MSGS = {};
			if (event) {
				$rootScope.$broadcast('gws-ws-disconnect');
			}
		}
	};
	
	WebsocketSrvc.connected = function() {
		return WebsocketSrvc.SOCKET ? true : false;
	};

	////////////////////////
	// Sync Protocol part //
	////////////////////////
	WebsocketSrvc.nextMid = function() {
		return sprintf('%7d', WebsocketSrvc.NEXT_MID++);
	};

	WebsocketSrvc.syncMessage = function(messageText) {
		var parts = explode(':', messageText, 4);
		var cmd = parts[0];
		if (parts[1] !== 'MID') {
			return false;
		}
		var mid = parts[2];
		var payload = parts[3];
		
		if (WebsocketSrvc.SYNC_MSGS[mid]) {
			WebsocketSrvc.SYNC_MSGS[mid].resolve(payload);
			delete WebsocketSrvc.SYNC_MSGS(mid);
		}
		
		return true;
	};
	
	/////////////////////////////
	// Send Queue on reconnect //
	/////////////////////////////
	WebsocketSrvc.startQueue = function() {
		console.log('WebsocketSrvc.startQueue()');
		if (WebsocketSrvc.QUEUE_INTERVAL === null) {
			WebsocketSrvc.QUEUE_INTERVAL = setInterval(WebsocketSrvc.flushQueue, WebsocketSrvc.QUEUE_SEND_MILLIS);
		}
	};
	
	WebsocketSrvc.flushQueue = function() {
		if (!WebsocketSrvc.connected()) {
			// TODO: Recon?
		}
		else {
			WebsocketSrvc.sendQueue();
		}
	};
	
	WebsocketSrvc.sendQueue = function() {
		if (WebsocketSrvc.QUEUE.length > 0) {
//			console.log('WebsocketSrvc.sendQueue()');
		}
	};
	
	//////////
	// Send //
	//////////

	WebsocketSrvc.sendJSONCommand = function(command, object, async=true) {
		return WebsocketSrvc.sendCommand(command, JSON.stringify(object), async);
	};
	
	WebsocketSrvc.sendCommand = function(command, payload, async=true) {
		var d = $q.defer();
		if (!WebsocketSrvc.connected()) {
//			WebsocketSrvc.QUEUE.push(messageText);
			d.reject();
		}
		else {
			if (!async) {
				var mid = WebsocketSrvc.NEXT_MID++;
				WebsocketSrvc.SYNC_MSGS[mid] = d;
				payload = sprintf('MID:%s:%s', mid, payload);
			}
			
			var messageText = GWF_USER.secret()+":"+command+":"+payload;
			
			WebsocketSrvc.send(messageText);
			
			if (async) {
				d.resolve();
			}
		}
		
		return d.promise;
	};
	
	WebsocketSrvc.send = function(messageText) {
		console.log('WebsocketSrvc.send()', messageText);
		WebsocketSrvc.SOCKET.send(messageText);
	};

});
