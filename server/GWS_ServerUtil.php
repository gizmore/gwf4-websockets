<?php
/**
 * UID:NICK:SECRET:CMD:PAYLOAD
 * 
 * @author gizmore
 */
final class GWS_ServerUtil
{
	public static $HANDLER;
	
	public static function getUserForMessage($msg, $allowGuests)
	{
#		echo "$msg\n";
		$parts = explode(':', $msg, 5);
		if (count($parts) !== 5) {
			return 'ERR_FORMAT';
		}
		if (!self::$HANDLER->validCommand($parts[3])) {
			return 'ERR_COMMAND';
		}
		if (false === ($user = (GWS_Global::getOrLoadUser($parts[1], $allowGuests)))) {
			return "ERR_COMMAND_USER";
		}
		$testID = $user->isGuest() ? $user->getGuestID() : $user->getID();
		if ($testID !== $parts[0]) {
			return "ERR_ID_MISMATCH";
		}
		if (self::secretForUser($user) !== $parts[2]) {
			return "ERR_SECRET";
		}
		GWS_Global::addUser($user);
		return $user;
	}
	
	public static function secretForUser(GWF_User $user)
	{
		return substr($user->getVar('user_password'), 13, 8);
	}
	
	public static function getUserForName($name)
	{
		return isset(GWS_Global::$USERS[$name]) ? GWS_Global::$USERS[$name] : false;
	}
	
	public static function getUserForConnection($conn)
	{
		foreach (GWS_Global::$USERS as $name => $user)
		{
			if (GWS_Global::getInterfaceConnection($user) === $conn) {
				return $user;
			}
		}
		return false;
	}

}