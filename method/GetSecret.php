<?php
final class Websockets_GetSecret extends GWF_Method
{
	public function execute()
	{
		header("Access-Control-Allow-Origin: file://");
		header("Access-Control-Allow-Credentials: true");
		
		$user = Module_GWF::instance()->getUserJS();
		$key = trim($this->module->binarySecret(), "'");
		$json = array(
			'user' => $user,
			'key' => $key
		);
		return json_encode($json);
	}
}