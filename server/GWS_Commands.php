<?php
class GWS_Commands
{
	const DEFAULT_MID = '0000000';
	const MID_LENGTH = 7;
	
	public function execute(GWF_User $user, $message)
	{
		$parts = explode(':', $message, 5);
		$methodName = 'cmd_'.$parts[3];
		$payload = $parts[4];
		$mid = self::DEFAULT_MID;
		if (substr($payload, 0, 4) === 'MID:') {
			$mid = substr($payload, 4, self::MID_LENGTH);
			$payload = substr($payload, 12);
		}
		return call_user_func(array($this, $methodName), $user, $payload, $mid);
	}
	
	public function validCommand($commandName)
	{
		$methodName = 'cmd_'.$commandName;
		return method_exists($this, $methodName);
	}
	
	public function disconnect(GWF_User $user)
	{
		GWS_Global::removeUser($user);
	}
	
	public static function payload($payload, $mid=self::DEFAULT_MID)
	{
		return $mid === self::DEFAULT_MID ? $payload : sprintf('MID:%7s:%s', $mid, $payload);
	}
	
	############
	### Init ###
	############
	public function init()
	{
		
	}
	
	#############
	### Timer ###
	#############
	public function timer()
	{
	
	}
	
	################
	### Commands ###
	################
	public function cmd_binary(GWF_User $user, $payload, $mid)
	{
		$packet = new GWS_Packet(255, $mid);
		$packet->addByte(255);
		$packet->addByte(0);
		$packet->addWord(0);
		$packet->addWord(256);
		$packet->addLong(3123456789);
		GWS_Global::send($user, $packet);
	}
	
	public function cmd_ping(GWF_User $user, $payload, $mid)
	{
		$clientVersion = $payload;
		GWS_Global::sendCommand($user, 'PONG', self::payload('1.0.0', $mid));
	}
	
	public function cmd_stats(GWF_User $user, $payload, $mid)
	{
		$payload = json_encode(array(
			'players' => count(GWS_Global::$USERS),
			'memory' => memory_get_usage(),
			'peak' => memory_get_peak_usage(true),
			'cpu' => -1.00,
		));
		GWS_Global::sendCommand($user, 'STATS', self::payload($payload, $mid));
	}
	
	public function cmd_user(GWF_User $user, $payload, $mid)
	{
		if (!($p = GWS_ServerUtil::getUserForName($payload)))
		{
			return GWS_Global::sendError($user, 'ERR_UNKNOWN_PLAYER');
		}
		$payload = json_encode(array(
			'player' => array_merge(array('name' => $p->getName(), 'hash' => $p->getStatsHash()), $p->playerDTO()),
		));
		GWS_Global::sendCommand($user, 'USER', self::payload($payload, $mid));
	}
	
}
