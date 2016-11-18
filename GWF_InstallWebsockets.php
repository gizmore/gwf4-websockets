<?php
final class GWF_InstallWebsockets
{
	public static function onInstall(Module_Websockets $module, $dropTable)
	{
		return GWF_ModuleLoader::installVars($module, array(
				'ws_url' => array(sprintf('ws://%s:34543', GWF_DOMAIN), 'text', '6', '255'),
				'wss_url' => array(sprintf('wss://%s:61221', GWF_DOMAIN), 'text', '7', '255'),
		));
	}
}