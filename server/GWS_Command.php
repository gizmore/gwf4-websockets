<?php
/**
 * GWS_Commands have to register via GWS_Commands::register($code, GWS_Command, $binary=true)
 * @author gizmore
 */
abstract class GWS_Command
{
	function init() {}
	
	abstract function execute(GWS_Message $msg);
}
