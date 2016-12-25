<?php
final class GWF_InstallWebsockets
{
	public static function onInstall(Module_Websockets $module, $dropTable)
	{
		return GWF_ModuleLoader::installVars($module, array(
				'ws_console_logging' => array('1', 'bool'),
				'ws_guest_connections' => array('1', 'bool'),
				'ws_url' => array(sprintf('ws://%s:34543', GWF_DOMAIN), 'text', '6', '255'),
				'ws_port' => array('34543', 'int', 1, 65535),
				'ws_timer_interval' => array('0.0', 'float', '0', '186400'),
				'ws_processor_path' => array(sprintf('%smodule/Websockets/server/GWS_Commands.php', GWF_PATH), 'text', '0', '255'),
				'ws_processor_class' => array('GWS_Commands', 'text', '0', '255'),
		));
	}
}
