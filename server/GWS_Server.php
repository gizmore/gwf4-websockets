<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

final class GWS_Server implements MessageComponentInterface
{
	private $gws;
	private $server;
	private $handler;
	private $allowGuests;
	private $consoleLog;
	
	public function mainloop($timerInterval=0)
	{
		GWF_Log::logMessage("GWS_Server::mainloop()");
		if ($timerInterval > 0)
		{
			$this->server->loop->addPeriodicTimer($timerInterval, array($this, 'mainTimer'));
		}
		$this->server->run();
	}
	
	public function mainTimer()
	{
		$this->handler->timer();
	}
	
	###############
	### Ratchet ###
	###############
	public function onOpen(ConnectionInterface $conn) {
		GWF_Log::logCron(sprintf("GWS_Server::onOpen()"));
	}
	
	public function onMessage(ConnectionInterface $from, $msg) {
// 		GWF_Log::logCron(sprintf("GWS_Server::onMessage(): %s", $msg));
		if (strlen($msg) > 511) 
		{
			$from->send('ERR:ERR_MSG_LENGTH_EXCEED:511');
			return;
		}
		$user = GWS_ServerUtil::getUserForMessage($msg, $this->allowGuests);
		if ($user instanceof GWF_User)
		{
			if (!GWS_Global::isConnected($user))
			{
				GWS_Global::setConnectionInterface($user, $from);
			}
			if ($this->consoleLog) {
				GWF_Log::logCron(sprintf("%s executes %s", $user->getName(), $message));
			}
			$this->handler->execute($user, $msg);
		}
		else
		{
			$from->send('ERR:'.$user);
		}
	}
	
	public function onClose(ConnectionInterface $conn) {
		GWF_Log::logCron(sprintf("GWS_Server::onClose()"));
		if ($user = GWS_ServerUtil::getUserForConnection($conn)) {
			$this->handler->disconnect($user);
		}
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e) {
		GWF_Log::logCron(sprintf("GWS_Server::onError()"));
	}
	
	############
	### Init ###
	############
	public function initGWSServer($handler, Module_Websockets $gws)
	{
		$this->handler = GWS_ServerUtil::$HANDLER = $handler;
		$this->gws = $gws; $port = $gws->cfgWebsocketPort();
		GWF_Log::logCron("GWS_Server::initGWSServer() Port $port");
		$this->allowGuests = $gws->cfgAllowGuestConnections();
		$this->consoleLog = GWS_Global::$LOGGING = $gws->cfgConsoleLogging();
		$this->server = IoServer::factory(new HttpServer(new WsServer($this)), $port);
		$this->handler->init();
		return true;
	}
}
