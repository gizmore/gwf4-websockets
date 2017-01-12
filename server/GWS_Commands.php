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
}
