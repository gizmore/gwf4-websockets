<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

final class GWS_Server implements MessageComponentInterface
{
	private $server;
	
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
		if ($user instanceof GWS_User) {
			if (!$user->isConnected()) {
				$user->setConnectionInterface($from);
				$user->rehash();
			}
			GWS_Commands::execute($user, $msg);
		}
		else {
			echo $user;
			$from->send('ERR:'.$user);
		}
	}
	
	public function onClose(ConnectionInterface $conn) {
		GWF_Log::logCron(sprintf("GWS_Server::onClose()"));
		if ($user = GWS_ServerUtil::getUserForConnection($conn)) {
			GWS_Commands::disconnect($user);
		}
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e) {
		GWF_Log::logCron(sprintf("GWS_Server::onError()"));
	}
	
	############
	### Init ###
	############
	public function initGWSServer()
	{
		GWF_Log::logMessage("GWS_Server::initTamagochiServer()");
		$this->server = IoServer::factory(new HttpServer(new WsServer($this)), 34543);
		return true;
	}
}
