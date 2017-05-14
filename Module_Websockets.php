<?php
/**
 * Websocket server module.
 * @author gizmore
 * @license MIT
 */
final class Module_Websockets extends GWF_Module
{
	private static $instance;
	public static function instance() { return self::$instance; }
	
	public function getVersion() { return 4.03; }
	public function getDefaultPriority() { return 20; }
	public function getDefaultAutoLoad() { return true; }
	public function onLoadLanguage() { return $this->loadLanguage('lang/websockets'); }
	public function onInstall($dropTable) { require_once 'GWF_InstallWebsockets.php'; return GWF_InstallWebsockets::onInstall($this, $dropTable); }
	
	public function cfgConsoleLogging() { return $this->getModuleVarBool('ws_console_logging', '1'); }
	public function cfgAllowGuestConnections() { return $this->getModuleVarBool('ws_guest_connections', '1'); }
	public function cfgForceCookies() { return $this->getModuleVarBool('ws_force_cookies', '0'); }
	
	public function cfgWebsocketURL() { return $this->getModuleVar('ws_url', sprintf('ws://%s:34543', GWF_DOMAIN)); }
	public function cfgWebsocketCert() { return $this->getModuleVar('ws_cert', ''); }
	public function cfgWebsocketPort() { return $this->getModuleVarInt('ws_port', '34543'); }
	public function cfgWebsocketBinary() { return $this->getModuleVarBool('ws_binary', '1'); }
	public function cfgTimerInterval() { return $this->getModuleVarFloat('ws_timer_interval', '0'); }
	public function cfgWebsocketProcessorPath() { return $this->getModuleVar('ws_processor_path', $this->defaultProcessorPath()); }
	public function cfgWebsocketProcessorClass() { return $this->getModuleVar('ws_processor_class', 'GWS_Commands'); }
	
	public function defaultProcessorPath() { return sprintf('%smodule/Websockets/server/GWS_Commands.php', GWF_PATH); }
	
	public function onStartup()
	{
		self::$instance = $this;
		$this->onLoadLanguage();
		if (GWF_Session::hasSession())
		{
			GWF_Website::addJavascriptInline($this->configScript());

			if (Module_GWF::instance()->cfgAngularApp())
			{
				$this->addJavascript('gws-message.js');
				$this->addJavascript('gws-command-service.js');
				$this->addJavascript('gws-connect-controller.js');
				$this->addJavascript('gws-stats-controller.js');
				$this->addJavascript('gws-websocket-service.js');
			}
		}
	}
	
	private function configScript()
	{
		return php_sapi_name() === 'cli' ? '' :
			sprintf(' GWF_CONFIG.ws_url = "%s"; GWF_CONFIG.wss_secret = "%s"; GWF_CONFIG.ws_binary = %s; GWF_CONFIG.ws_binary_secret = %s;', 
				$this->cfgWebsocketURL(), $this->websocketSecret(), $this->cfgWebsocketBinary()?'true':'false', $this->binarySecret());
	}
	
	public function websocketSecret()
	{
		$user = GWF_User::getStaticOrGuest();
		if ($user->persistentGuest() && $user->isUser())
		{
			$uid = $user->isGuest() ? $user->getGuestID() : $user->getID();
			$name = $user->getName();
			$secret = $user->isGuest() ? sha1(GWF_SECRET_SALT.GWF_Session::getSessSID().GWF_SECRET_SALT) : $user->getVar('user_password');
			$secret = substr($secret, 13, 8);
			return "$uid:$name:$secret";
		}
		else
		{
			return '0:0:x';
		}
	}
	
	public function binarySecret()
	{
		return sprintf("'%s'", GWF_Session::getCookieValue());
	}
	
	public function sidebarContent($bar)
	{
		if ( ($user = GWF_Session::getUser()) || ($this->cfgAllowGuestConnections()) )
		{
			if ($bar === 'left')
			{
				return $this->template('connect-bar.php');
			}
			if ($bar === 'right')
			{
				return $this->template('stats-bar.php');
			}
		}
	}
	
}