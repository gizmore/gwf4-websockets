'use strict';
angular.module('gwf4').
service('WebsocketSrvc', function($q, $rootScope, ErrorSrvc, CommandSrvc, LoadingSrvc) {
	
	var WebsocketSrvc = this;
	
	WebsocketSrvc.SYNC_MSGS = {};
	WebsocketSrvc.SOCKET = null;
	WebsocketSrvc.CONNECTED = false;
	
	////////////
	// Config //
	////////////
	WebsocketSrvc.CONFIG = {
		url: GWF_CONFIG.ws_url,
		binary: GWF_CONFIG.ws_binary,
		autoConnect: false,
		reconnect: true, // @TODO reconnect
		reconnectTimeout: 10000,
		keepQueue: true, // @TODO Try to resend queue after reconnect 
	};
	WebsocketSrvc.configure = function(config) {
		console.log('WebsocketSrvc.configure()', config);
		WebsocketSrvc.CONFIG = config;
		if (config.autoConnect) {
			return WebsocketSrvc.connect();
		}
	}; WebsocketSrvc.configure(WebsocketSrvc.CONFIG);
	
	
	////////////////
	// Connection //
	////////////////
	WebsocketSrvc.withConnection = function(url) {
		console.log('WebsocketSrvc.withConnection()', url);
		WebsocketSrvc.CONFIG.url = url || WebsocketSrvc.CONFIG.url;
		if (WebsocketSrvc.connected()) {
			var defer = $q.defer();
			defer.resolve();
			return defer.promise;
		}
		return WebsocketSrvc.connect(url);
	};
	
	WebsocketSrvc.withConn = function(callback) {
		return WebsocketSrvc.withConnection().then(callback, WebsocketSrvc.connectionFailure);
	};
	
	WebsocketSrvc.connectionFailure = function(error) {
		ErrorSrvc.showError(error, 'Websocket');
	}
	
	WebsocketSrvc.connect = function(url) {
		url = url || WebsocketSrvc.CONFIG.url;
		console.log('WebsocketSrvc.connect()', url);
		var defer = $q.defer();
		if (WebsocketSrvc.SOCKET == null) {
			LoadingSrvc.addTask('wsconnect');
			var ws = WebsocketSrvc.SOCKET = new WebSocket(url);
			if (WebsocketSrvc.CONFIG.binary) {
				ws.binaryType = 'arraybuffer';
			}
			ws.onopen = function() {
				LoadingSrvc.stopTask('wsconnect');
				WebsocketSrvc.startQueue();
		    	defer.resolve();
		    	WebsocketSrvc.authenticate().then(function(result){
			    	WebsocketSrvc.CONNECTED = true;
		    		$rootScope.$broadcast('gws-ws-open');
		    	});
			};
		    ws.onclose = function() {
				LoadingSrvc.stopTask('wsconnect');
		    	WebsocketSrvc.disconnect(true);
		    	if (WebsocketSrvc.CONNECTED) {
			    	WebsocketSrvc.CONNECTED = false;
		    		$rootScope.$broadcast('gws-ws-close');
		    	}
		    };
		    ws.onerror = function(error) {
		    	WebsocketSrvc.disconnect(true);
				defer.reject(error);
		    };
		    ws.onmessage = function(message) {
		    	if (message.data instanceof ArrayBuffer) {
		    		WebsocketSrvc.onBinaryMessage(message);
		    	}
		    	else {
		    		WebsocketSrvc.onMessage(message);
		    	}
		    };
		}
		else {
			defer.reject();
		}
		return defer.promise;
	};
	
	WebsocketSrvc.onMessage = function(message) {
    	console.log('WebsocketSrvc.onMessage()', message);
    	if (message.data.indexOf('ERR:') === 0) {
    		ErrorSrvc.showError(message.data, 'User Error');
    	}
    	else if (message.data.indexOf(':MID:') >= 0) {
    		if (!WebsocketSrvc.syncMessage(message.data)) {
    			WebsocketSrvc.processMessage(mesage.data);
    		}
    	} else {
			WebsocketSrvc.processMessage(message.data);
    	}
	};

	WebsocketSrvc.onBinaryMessage = function(message) {
    	console.log('WebsocketSrvc.onBinaryMessage()', message);
		var gwsMessage = new GWS_Message(message.data);
		gwsMessage.dump();
		var command = gwsMessage.readCmd();
		if (gwsMessage.isSync()) {
			var mid = gwsMessage.readMid();
			if (WebsocketSrvc.SYNC_MSGS[mid]) {
				WebsocketSrvc.SYNC_MSGS[mid].resolve(gwsMessage);
				WebsocketSrvc.SYNC_MSGS[mid] = undefined; // TODO delete array element
			}
		}
		$rootScope.$broadcast('gws-ws-message', gwsMessage);
	};

	WebsocketSrvc.processMessage = function(messageText) {
		console.log('ConnectCtrl.processMessage()', messageText);
		var command = messageText.substrUntil(':');
		if (CommandSrvc[command]) {
			CommandSrvc[command](messageText.substrFrom(':'));
		}
		else {
	    	$rootScope.$broadcast('gws-ws-message', messageText);
		}
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
	
	//////////
	// Auth //
	//////////
	WebsocketSrvc.connected = function() {
		return WebsocketSrvc.SOCKET ? true : false;
	};
	
	WebsocketSrvc.authenticate = function() {
		var w = WebsocketSrvc;
		return w.sendBinary(GWS_Message().cmd(0x0001).sync().writeString(GWF_CONFIG.ws_binary_secret)).then(w.authenticated, w.authFailure);
	};

	WebsocketSrvc.authenticated = function(payload) {
		console.log('WebsocketSrvc.authenticated()', payload);
		GWF_USER.update(JSON.parse(payload));
	};

	WebsocketSrvc.authFailure = function(error) {
		console.log('WebsocketSrvc.authFailure()', error);
		ErrorSrvc.showError(error, 'Websocket Authentication');
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
			WebsocketSrvc.SYNC_MSGS[mid] = undefined;
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
	WebsocketSrvc.sendCommand = function(command, payload='', async=true) {
		var d = $q.defer();
		if (!WebsocketSrvc.connected()) {
//			WebsocketSrvc.QUEUE.push(messageText);
			d.reject();
		}
		else {
			if (!async) {
				var mid = GWS_Message.nextMid();
				WebsocketSrvc.SYNC_MSGS[mid] = d;
				payload = sprintf('MID:%s:%s', mid, payload);
			}
			WebsocketSrvc.send(command+":"+payload);
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
	
	
	// Binary //
	WebsocketSrvc.sendBinary = function(gwsMessage) {
		var d = $q.defer();
		if (WebsocketSrvc.connected()) {
			if (gwsMessage.SYNC > 0) {
				WebsocketSrvc.SYNC_MSGS[gwsMessage.SYNC] = d;
			}
			else {
				d.resolve();
			}
			gwsMessage.dump();
			WebsocketSrvc.SOCKET.send(gwsMessage.binaryBuffer());
		}
		else {
			d.reject();
		}
		return d.promise;
	};

	return WebsocketSrvc;
});
