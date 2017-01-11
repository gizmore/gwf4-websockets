<?php
class GWS_Commands
{
	const DEFAULT_MID = '0000000';
	const MID_LENGTH = 7;
	
	public function executeTextMessage(GWS_Message $message)
	{
		$methodName = 'cmd_'.$message->cmd();
		if (method_exists($this, $methodName))
		{
			return call_user_func(array($this, $methodName), $message);
		}
	}
	
	public function executeBinaryMessage(GWS_Message $message)
	{
		$method_name = sprintf('xcmd_%04X', $message->cmd());
		if (method_exists($this, $method_name))
		{
			$callback = array($this, $method_name);
			call_user_func($callback, $message);
		}
	}
	
	#######
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
	public function xcmd_0000(GWS_Message $msg)
	{
		$msg->replyError(0x0404); // Ney
	}

	public function xcmd_0001(GWS_Message $msg)
	{
		$msg->replyError(0x0005); // already authed
	}
	
	public function xcmd_0002(GWS_Message $msg)
	{
		$msg->replyBinary(0x0002); // pong
	}

	public function xcmd_0101(GWS_Message $msg)
	{
		$payload = '';
		$payload.= $msg->write32(memory_get_usage());
		$payload.= $msg->write32(memory_get_peak_usage(true));
		$payload.= $msg->write16(count(GWS_Global::$USERS));
		$payload.= $msg->write8(100);
		$msg->replyBinary(0x0101, $payload);
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
