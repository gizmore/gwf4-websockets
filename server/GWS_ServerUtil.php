<?php
/**
 * UID:NICK:SECRET:CMD:PAYLOAD
 * 
 * @author gizmore
 */
final class GWS_ServerUtil
{
	public static $HANDLER;
	
	public static function getUserForMessage($msg)
	{
		$parts = explode(':', $msg, 5);
		if (count($parts) !== 5) {
			return 'ERR_FORMAT';
		}
		if (!self::$HANDLER->validCommand($parts[3])) {
			return 'ERR_COMMAND';
		}
		if (false === ($user = (GWS_Global::getOrLoadUser($parts[1])))) {
			return "ERR_USER_NAME";
		}
		if ($user->getVar('user_id') !== $parts[0]) {
			return "ERR_ID_MISMATCH";
		}
		if (substr($user->getVar('user_password'), 13, 8) !== $parts[2]) {
			return "ERR_SECRET";
		}
		GWS_Global::addUser($user);
		return $user;
	}
	
	public static function getUserForName($name)
	{
		return isset(GWS_Global::$USERS[$name]) ? GWS_Global::$USERS[$name] : false;
	}
	
	public static function getUserForConnection($conn)
	{
		foreach (GWS_Global::$USERS as $name => $user)
		{
			if ($user->getInterfaceConnection() === $conn) {
				return $user;
			}
		}
		return false;
	}

}