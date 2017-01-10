<?php
/**
 * UID:NICK:SECRET:CMD:PAYLOAD
 * 
 * @author gizmore
 */
final class GWS_ServerUtil
{
	public static $HANDLER;
	
// 	public static function getUserForMessage($msg, $allowGuests)
// 	{
// #		echo "$msg\n";
// 		$parts = explode(':', $msg, 5);
// 		if (count($parts) !== 5) {
// 			return 'ERR_FORMAT';
// 		}
// 		if (!self::$HANDLER->validCommand($parts[3])) {
// 			return 'ERR_COMMAND';
// 		}
// 		if (false === ($user = (GWS_Global::getOrLoadUser($parts[1], $allowGuests)))) {
// 			return "ERR_COMMAND_USER";
// 		}
// 		$testID = $user->isGuest() ? $user->getGuestID() : $user->getID();
// 		if ($testID !== $parts[0]) {
// 			return "ERR_ID_MISMATCH";
// 		}
// 		if (self::secretForUser($user) !== $parts[2]) {
// 			return "ERR_SECRET";
// 		}
// 		GWS_Global::addUser($user);
// 		return $user;
// 	}
	
// 	public static function getUserForBinaryMessage($msg, $allowGuests)
// 	{
		
// 	}
	
// 	public static function secretForUser(GWF_User $user)
// 	{
// 		return substr($user->getVar('user_password'), 13, 8);
// 	}
	
// 	public static function getUserForName($name)
// 	{
// 		return isset(GWS_Global::$USERS[$name]) ? GWS_Global::$USERS[$name] : false;
// 	}
	
// 	public static function getUserForConnection($conn)
// 	{
// 		foreach (GWS_Global::$USERS as $name => $user)
// 		{
// 			if (GWS_Global::getInterfaceConnection($user) === $conn) {
// 				return $user;
// 			}
// 		}
// 		return false;
// 	}

	###############
	### Hexdump ###
	###############
	public static function hexdump($data, $newline="\n")
	{
		static $from = '';
		static $to = '';
	
		static $width = 16; # number of bytes per line
	
		static $pad = '.'; # padding for non-visible characters
	
		if ($from==='')
		{
			for ($i=0; $i<=0xFF; $i++)
			{
				$from .= chr($i);
				$to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
			}
		}
	
		$hex = str_split(bin2hex($data), $width*2);
		$chars = str_split(strtr($data, $from, $to), $width);
	
		$offset = 0;
		foreach ($hex as $i => $line)
		{
			echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
			$offset += $width;
		}
	}
}
