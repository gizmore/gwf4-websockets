<?php
final class GWS_Global
{
	public static $USERS = array();
	public static $CONNECTIONS = array();
	
	public static function addUser(GWF_User $user)
	{
		self::$USERS[$user->getName()] = $user;
	}
	
	public static function removeUser(GWF_User $user, $reason='NO_REASON')
	{
		if (!isset(self::$USERS[$user->getName()])) {
			return false;
		}
		GWS_Global::disconnect($user, $reason);
		unset(self::$USERS[$user->getName()]);
		return true;
	}
	
	public static function getUser($name)
	{
		return isset(self::$USERS[$name]) ? self::$USERS[$name] : false;
	}
	
	public static function getOrLoadUser($name, $allowGuests)
	{
		if (false !== ($user = self::getUser($name)))
		{
			return $user;
		}
		return self::loadUser($name, $allowGuests);
	}
	

	###############
	### Private ###
	###############
	private static function loadUser($name, $allowGuests)
	{
		$letter = $name[0];
		if (($letter >= '0') && ($letter <= '9'))
		{
			if ($allowGuests) {
				return self::getUserBySessID($name);
			} else {
				return false;
			}
		}
		else
		{
			return GWF_User::getByName($name);
		}
	}
	
	private static function getUserBySessID($number)
	{
		$number = (string)$number;
		if (!isset(self::$USERS[$number]))
		{
			$user = GWF_Guest::getGuest($number);
			$user->setVar('user_password', sha1(GWF_SECRET_SALT.$number.GWF_SECRET_SALT));
			self::$USERS[$number] = $user;
		}
		return self::$USERS[$number];
	}
	
	##################
	### Connection ###
	##################
	public static function sendError(GWF_User $user, $i18nKey, $args=array())
	{
		GWF_Log::logCron(sprintf("%s: %s", $user->getName(), $i18nKey));
		return self::sendCommand($user, 'ERR', $i18nKey);
	}
	
	public static function sendJSONCommand(GWF_User $user, $command, $object)
	{
		return self::sendCommand($user, $command, json_encode($object));
	}
	
	public static function sendCommand(GWF_User $user, $command, $payload)
	{
		return self::send($user, "$command:$payload");
	}
	
	public static function send(GWF_User $user, $messageText)
	{
		if (self::isConnected($user)) {
			GWF_Log::logCron(sprintf('%s << %s', $user->getName(), $messageText));
			self::$CONNECTIONS[$user->getName()]->send($messageText);
		}
	}
	
	public static function disconnect(GWF_User $user, $reason="NO_REASON")
	{
		if (self::isConnected($user))
		{
			self::send($user, "CLOSE:".$reason);
			unset(self::$CONNECTIONS[$user->getName()]);
		}
	}
	
	public static function isConnected(GWF_User $user)
	{
		return isset(self::$CONNECTIONS[$user->getName()]);
	}
	

	public static function setConnectionInterface(GWF_User $user, $conn)
	{
		if (self::isConnected($user)) {
			self::disconnect($user);
		}
		self::$CONNECTIONS[$user->getName()] = $conn;
	}
	
	public static function getInterfaceConnection(GWF_User $user)
	{
		return isset(self::$CONNECTIONS[$user->getName()]) ? self::$CONNECTIONS[$user->getName()] : false;
	}
	
}
