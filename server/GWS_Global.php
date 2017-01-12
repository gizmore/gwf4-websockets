<?php
final class GWS_Global
{
	public static $LOGGING = false;
	public static $USERS = array();
	public static $CONNECTIONS = array();
	
	public static function addUser(GWF_User $user)
	{
		self::$USERS[$user->getName()] = $user;
	}
	
	public static function removeUser(GWF_User $user, $reason='NO_REASON')
	{
		if (isset(self::$USERS[$user->getName()]))
		{
			GWS_Global::disconnect($user, $reason);
			unset(self::$USERS[$user->getName()]);
		}
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
	public static function disconnect(GWF_User $user, $reason="NO_REASON")
	{
		if (self::isConnected($user))
		{
			self::getConnectionInterface($user)->send($user, "CLOSE:".$reason);
			unset(self::$CONNECTIONS[$user->getID()]);
		}
	}
	
	public static function isConnected($user)
	{
		return isset(self::$CONNECTIONS[$user->getID()]);
	}
	

	public static function setConnectionInterface($user, $conn)
	{
		if (self::isConnected($user))
		{
			self::disconnect($user);
		}
		self::$CONNECTIONS[$user->getID()] = $conn;
	}
	
	public static function getConnectionInterface($user)
	{
		return isset(self::$CONNECTIONS[$user->getID()]) ? self::$CONNECTIONS[$user->getID()] : false;
	}

}
