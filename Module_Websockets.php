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
	
	public function cfgWebsocketProcessorPath() { return $this->getModuleVar('ws_processor_path', sprintf('%smodule/Websockets/server/GWS_Commands.php', GWF_PATH)); }
	public function cfgWebsocketProcessorClass() { return $this->getModuleVar('ws_processor_class', 'GWS_Commands'); }
	public function cfgWebsocketURL() { return $this->getModuleVar('ws_url', sprintf('ws://%s:34543', GWF_DOMAIN)); }
	public function cfgWebsocketTLSURL() { return $this->getModuleVar('wss_url', sprintf('wss://%s:61221', GWF_DOMAIN)); }
	
	public function onStartup()
	{
		self::$instance = $this;
		$this->onLoadLanguage();
		
		GWF_Website::addJavascriptInline($this->configScript());
	}
	
	private function configScript()
	{
		return sprintf('GWF_CONFIG.ws_url = "%s"; GWF_CONFIG.wss_url = "%s"; ', $this->cfgWebsocketURL(), $this->cfgWebsocketTLSURL());
	}
	
}