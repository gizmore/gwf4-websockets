<?php
final class Module_Websockets extends GWF_Module
{
	private static $instance;
	public static function instance() { return self::$instance; }
	
	public function getVersion() { return 1.03; }
	public function getDefaultPriority() { return 20; }
	public function getDefaultAutoLoad() { return true; }
	public function onLoadLanguage() { return $this->loadLanguage('lang/websockets'); }
	public function onInstall($dropTable) { require_once 'GWF_InstallWebsockets.php'; return GWF_InstallWebsockets::onInstall($this, $dropTable); }
	
	public function cfgWebsocketURL() { return $this->getModuleVar('ws_url', sprintf('ws://%s:34543', GWF_DOMAIN)); }
	public function cfgWebsocketTLSURL() { return $this->getModuleVar('wss_url', sprintf('wss://%s:61221', GWF_DOMAIN)); }
	
	public function onStartup()
	{
		self::$instance = $this;
		$this->onLoadLanguage();
	}
	
}