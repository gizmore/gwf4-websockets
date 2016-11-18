<?php
final class GWS_Global
{
	public static $USERS = array();
	
	public static function addUser(GWF_User $user)
	{
		self::$USERS[$user->getName()] = $user;
	}
	
	public static function removeUser(GWF_User $user, $reason='NO_REASON')
	{
		if (!isset(self::$USERS[$user->getName()])) {
			return false;
		}
		
		$user->disconnect($reason);
		unset(self::$USERS[$user->getName()]);
		return true;
	}
	
	public static function getUser($name)
	{
		return isset(self::$USERS[$name]) ? self::$USERS[$name] : false;
	}
	
	public static function getOrLoadUser($name)
	{
		if (false !== ($user = self::getUser($name))) {
			return $user;
		}
		return self::loadUser($name);
	}
	

	###############
	### Private ###
	###############
	private static function loadUser($name)
	{
		return GWF_User::getByName($nane);
	}
	
}
