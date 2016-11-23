<?php
final class Module_Websockets extends GWF_Module
{
	private static $instance;
	public static function instance() { return self::$instance; }
	
	public function getVersion() { return 1.04; }
	public function getDefaultPriority() { return 20; }
	public function getDefaultAutoLoad() { return true; }
	public function onLoadLanguage() { return $this->loadLanguage('lang/websockets'); }
	public function onInstall($dropTable) { require_once 'GWF_InstallWebsockets.php'; return GWF_InstallWebsockets::onInstall($this, $dropTable); }
	
	public function cfgAllowGuestConnections() { return $this->getModuleVarBool('ws_guest_connections', '1'); }
	public function cfgWebsocketProcessorPath() { return $this->getModuleVar('ws_processor_path', $this->defaultProcessorPath()); }
	public function cfgWebsocketProcessorClass() { return $this->getModuleVar('ws_processor_class', 'GWS_Commands'); }
	public function cfgWebsocketURL() { return $this->getModuleVar('ws_url', sprintf('ws://%s:34543', GWF_DOMAIN)); }
	public function cfgWebsocketTLSURL() { return $this->getModuleVar('wss_url', sprintf('wss://%s:61221', GWF_DOMAIN)); }
	
	public function defaultProcessorPath() { return sprintf('%smodule/Websockets/server/GWS_Commands.php', GWF_PATH); }
	
	public function onStartup()
	{
		self::$instance = $this;
		$this->onLoadLanguage();
		if (GWF_Session::hasSession()) {
			GWF_Website::addJavascriptInline($this->configScript());
			$this->addJavascript('gws-command-service.js');
			$this->addJavascript('gws-connect-controller.js');
			$this->addJavascript('gws-stats-controller.js');
			$this->addJavascript('gws-websocket-service.js');
		}
	}
	
	private function configScript()
	{
		return php_sapi_name() === 'cli' ? '' :
			sprintf(' GWF_CONFIG.ws_url = "%s"; GWF_CONFIG.wss_url = "%s"; GWF_CONFIG.wss_secret = "%s";', 
				$this->cfgWebsocketURL(), $this->cfgWebsocketTLSURL(), $this->websocketSecret());
	}
	
	private function websocketSecret()
	{
		$user = GWF_User::getStaticOrGuest();
		$uid = $user->getID();
		$name = $user->getID() > 0 ? $user->getName() : GWF_Session::getSessSID();
		$secret = $user->getID() > 0 ? $user->getVar('user_password') : sha1(GWF_SECRET_SALT.GWF_Session::getSessSID().GWF_SECRET_SALT);
		$secret = substr($secret, 13, 8);
		return "$uid:$name:$secret";
	}
	
	public function sidebarContent($bar)
	{
		if ( ($user = GWF_Session::getUser()) || ($this->cfgAllowGuestConnections()) )
		{
			if ($bar === 'left')
			{
				return $this->template('connect-bar.php');
			}
		}
	}
	
}