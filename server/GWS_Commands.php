<?php
require_once 'GWS_Command.php';

require_once 'commands/GWSC_Stats.php';

/**
 * Command handler base class.
 * Override this and set in websocket module config
 * @author gizmore
 */
class GWS_Commands
{
	const MID_LENGTH = 7; # Sync Message ID
	const DEFAULT_MID = '0000000'; # Sync Message ID
	
	################
	### Commands ###
	################
	/**
	 * 
	 * @var array(GWS_Command)
	 */
	public static $COMMANDS = array();
	public static function register($code, GWS_Command $command, $binary=true)
	{
		if (isset(self::$COMMANDS[$code]))
		{
			throw new Exception(sprintf("duplicate GWS_Command code: '%s' for %s", $code, get_class($command)));
		}
		$command->init();
		self::$COMMANDS[$code] = $command;
	}

	############
	### Exec ###
	############
	public function executeMessage(GWS_Message $message)
	{
		$cmd = $message->cmd();
		if (!isset(self::$COMMANDS[$cmd]))
		{
			throw new Exception("Unknown command");
		}
		self::$COMMANDS[$cmd]->execute($message);
	}

	################
	### Override ###
	################
	public function init() {}
	public function timer() {}
	public function connect(GWF_User $user) {}
	public function disconnect(GWF_User $user) {}
}
