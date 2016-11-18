<?php
final class GWF_InstallWebsockets
{
	public static function onInstall(Module_Websockets $module, $dropTable)
	{
		return GWF_ModuleLoader::installVars($module, array(
				'ws_processor_path' => array(sprintf('%smodule/Websockets/server/GWS_Commands.php', GWF_PATH), 'text', '0', '255'),
				'ws_processor_class' => array('GWS_Commands', 'text', '0', '255'),
				'ws_url' => array(sprintf('ws://%s:34543', GWF_DOMAIN), 'text', '6', '255'),
				'wss_url' => array(sprintf('wss://%s:61221', GWF_DOMAIN), 'text', '7', '255'),
		));
	}
}
