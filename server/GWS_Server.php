<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once 'GWS_Message.php';

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
			$this->server->loop->addPeriodicTimer($timerInterval, array($this->handler, 'timer'));
		}
		$this->server->run();
	}
	
	###############
	### Ratchet ###
	###############
	public function onOpen(ConnectionInterface $conn)
	{
		GWF_Log::logCron(sprintf("GWS_Server::onOpen()"));
	}

	public function onMessage(ConnectionInterface $from, $data)
	{
		printf("%s >> %s\n", $from->user() ? $from->user()->displayName() : '???', $data);
		$message = new GWS_Message($data, $from);
		$message->readTextCmd();
		if ($from->user())
		{
			$this->handler->executeTextMessage($message);
		}
		else
		{
			$message->replyError(0x0002);
		}
	}
	
	public function onBinaryMessage(ConnectionInterface $from, $data)
	{
		printf("%s >> BIN\n", $from->user() ? $from->user()->displayName() : '???');
		echo GWS_ServerUtil::hexdump($data);
		$message = new GWS_Message($data, $from);
		$message->readCmd();
		if (!$from->user())
		{
			$this->onAuthBinary($message);
		}
		else
		{
			$this->handler->executeBinaryMessage($message);
		}
	}
	
	public function onAuthBinary(GWS_Message $message)
	{
		if (!$message->cmd() === 0x0001)
		{
			$message->replyError(0x0001);
		}
		elseif (!$cookie = $message->readString())
		{
			$message->replyError(0x0002);
		}
		elseif (!GWF_Session::reload($cookie, false, $message->conn()->getRemoteAddress()))
		{
			$message->replyError(0x0003);
		}
		elseif ( (!($user = GWF_User::getStaticOrGuest())) || ((!$user->persistentGuest())))
		{
			$message->replyError(0x0004);
		}
		else
		{
			$message->conn()->setUser($user);
			GWS_Global::addUser($user);
			GWS_Global::setConnectionInterface($user, $message->conn());
			GWF_Session::commit();
			$message->replyText('AUTH', json_encode($user->getGDODataFields(array('user_name', 'user_guest_name', 'user_id', 'user_credits'))));
			$this->handler->connect($user);
		}
	}
	
	public function onClose(ConnectionInterface $conn)
	{
		GWF_Log::logCron(sprintf("GWS_Server::onClose()"));
		if ($user = $conn->user())
		{
			$conn->setUser(false);
			GWS_Global::removeUser($user);
			$this->handler->disconnect($user);
		}
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		GWF_Log::logCron(sprintf("GWS_Server::onError()"));
	}
	
	############
	### Init ###
	############
	public function initGWSServer($handler, Module_Websockets $gws)
	{
		
		$this->handler = GWS_ServerUtil::$HANDLER = $handler;
		$this->gws = $gws;
		$port = $gws->cfgWebsocketPort();
		GWF_Log::logCron("GWS_Server::initGWSServer() Port $port");
		$this->allowGuests = $gws->cfgAllowGuestConnections();
		$this->consoleLog = GWS_Global::$LOGGING = $gws->cfgConsoleLogging();
		$this->server = IoServer::factory(new HttpServer(new WsServer($this)), $port, $this->socketOptions());
		$this->handler->init();
		return true;
	}
	
	private function socketOptions()
	{
		$pemCert = trim($this->gws->cfgWebsocketCert());
		if (empty($pemCert))
		{
			return array();
		}
		else
		{
			return array(
				'ssl' => array(
					'local_cert' => $pemCert,
				),
			);
		}
	}
}
