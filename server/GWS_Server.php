<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

final class GWS_Server implements MessageComponentInterface
{
	private $server;
	private $handler;
	
	public function mainloop()
	{
		GWF_Log::logMessage("GWS_Server::mainloop()");
		$this->server->run();
	}
	
	###############
	### Ratchet ###
	###############
	public function onOpen(ConnectionInterface $conn) {
		GWF_Log::logCron(sprintf("GWS_Server::onOpen()"));
	}
	
	public function onMessage(ConnectionInterface $from, $msg) {
// 		GWF_Log::logCron(sprintf("GWS_Server::onMessage(): %s", $msg));
		if (strlen($msg) > 511) {
			$from->send('ERR:ERR_MSG_LENGTH_EXCEED:511');
			return;
		}
		$user = GWS_ServerUtil::getUserForMessage($msg);
		if ($user instanceof GWF_User) {
			if (!GWS_Global::isConnected($user)) {
				GWS_Global::setConnectionInterface($user, $from);
			}
			$this->handler->execute($user, $msg);
		}
		else {
			$from->send('ERR:ERR_SERVER');
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
	public function initGWSServer($handler)
	{
		GWF_Log::logMessage("GWS_Server::initTamagochiServer()");
		GWS_ServerUtil::$HANDLER = $handler;
		$this->handler = $handler;
		$this->server = IoServer::factory(new HttpServer(new WsServer($this)), 34543);
		return true;
	}
}
