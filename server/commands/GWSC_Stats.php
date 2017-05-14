<?php
final class GWSC_Stats extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$payload = '';
		$payload.= $msg->write32(memory_get_usage());
		$payload.= $msg->write32(memory_get_peak_usage(true));
		$payload.= $msg->write16(count(GWS_Global::$USERS));
		$payload.= $msg->write8(100);
		$msg->replyBinary($msg->cmd(), $payload);
		
	}
}

GWS_Commands::register(0x0201, new GWSC_Stats());
