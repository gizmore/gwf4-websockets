<?php
final class GWS_Message
{
	private $command;
	private $from;
	private $mid = 0;
	private $data;
	private $index = 0;
	
	public function __construct($binary, $from)
	{
		$this->data = $binary;
		$this->from = $from;
	}
	public function conn() { return $this->from; }
	/**
	 * @return GWF_User
	 */
	public function user() { return $this->from->user(); }
	public function cmd() { return $this->command; }
	public function index($index=-1) { $this->index = $index < 0 ? $this->index : $index; return $this->index; }
	public function isSync() { return $this->mid > 0; }
	
	#############
	### Reply ###
	#############
	public function replyText($command, $data='')
	{
		$payload = $this->mid > 0 ? "$command:MID:$this->mid:$data" : "$command:$data";
		GWF_Log::logWebsocket(sprintf("%s << %s\n", $this->user() ? $this->user()->displayName() : '???', $payload));
		return $this->from->send($payload);
	}
	
	public function replyBinary($command, $data='')
	{
		GWF_Log::logWebsocket(sprintf("%s << BIN\n", $this->user() ? $this->user()->displayName() : '???'));
		$command |= $this->mid > 0 ? 0x8000 : 0;
		$payload = '';
		$payload.= $this->write16($command);
		$payload.= $this->mid > 0 ? $this->write24($this->mid) : '';
		$payload.= $data;
		GWS_ServerUtil::hexdump($payload);
		return $this->from->sendBinary($payload);
	}
	
	public function replyError($code)
	{
		return $this->replyErrorMessage($code, '');
	}

	public function replyErrorMessage($code, $message)
	{
		return $this->replyBinary(0x0000, $this->write16($code).$this->writeString($message));
	}
	
	##############
	### Reader ###
	##############
	public function readPayload() { return $this->data; }
	public function readJSON() { return json_encode($this->data); }
	public function read8($signed=true, $index=-1) { return $this->readN(1, $signed, $index); }
	public function read16($signed=true, $index=-1) { return $this->readN(2, $signed, $index); }
	public function read24($signed=true, $index=-1) { return $this->readN(3, $signed, $index); }
	public function read32($signed=true, $index=-1) { return $this->readN(4, $signed, $index); }
	public function read8u($index=-1) { return $this->readN(1, false, $index); }
	public function read16u($index=-1) { return $this->readN(2, false, $index); }
	public function read24u($index=-1) { return $this->readN(3, false, $index); }
	public function read32u($index=-1) { return $this->readN(4, false, $index); }
	public function readFloat($index=-1) { $p = unpack("f", $this->readChars(4, $index)); return array_pop($p); }
	public function readChar($index=-1) { return $this->readChars(1, $index); }
	public function readChars($num, $index=-1)
	{
		$chars = substr($this->data, $this->index($index), $num);
		$this->index += $num;
		return strrev($chars);
	}
	
	public function readN($bytes, $signed=true, $index=-1)
	{
		$index = $this->index($index);
		$back = 0;
		for ($i = 0; $i < $bytes; $i++)
		{
			$back <<= 8;
			$back += ord($this->data[$index++]);
		}
		if ($signed)
		{
			$half = pow(2, ($bytes*8)-1);
			$back = $back > $half ? -$half+$back-$half : $back;
		}
		$this->index = $index;
		return $back;
	}
	public function readString($index=-1)
	{
		$string = '';
		$this->index($index);
		while ($char = $this->read8()) {
			$string .= chr($char);
		};
		return urldecode($string);
	}

	public function readCmd()
	{
		$cmd = $this->read16u();
		if (($cmd & 0x8000) > 0) {
			$this->mid = $this->read24();
		}
		$this->command = $cmd & 0x7FFF;
		return $this;
	}
	
	public function readTextCmd()
	{
		$firstCol = strpos($this->data, ':');
		$numParts = strpos($this->data, ':MID:') === $firstCol ? 4 : 2;
		$parts = explode(':', $this->data, $numParts);
		if ($numParts === 4)
		{
			$this->mid = $parts[2];
		}
		$this->command = $parts[0];
		$this->data = array_pop($parts);
		return $this;
	}
	
	
	##############
	### Writer ###
	##############
	public function writeFloat($float) { return self::wrF($float); }
	public function writeDouble($double) { return self::wrD($double); }
	public function writeString($string) { return self::wrS($string); }
	public function write8($value) { return self::wrN(1, $value); }
	public function write16($value) { return self::wrN(2, $value); }
	public function write24($value) { return self::wrN(3, $value); }
	public function write32($value) { return self::wrN(4, $value); }
	public function write64($value) { return self::wrN(8, $value); }
	public static function wr8($value) { return self::wrN(1, $value); }
	public static function wr16($value) { return self::wrN(2, $value); }
	public static function wr24($value) { return self::wrN(3, $value); }
	public static function wr32($value) { return self::wrN(4, $value); }
	public static function wr64($value) { return self::wrN(8, $value); }
	public static function wrF($float) { return pack("f", floatval($float)); }
	public static function wrD($double) { return pack("d", doubleval($double)); }
	public static function wrS($string) { return urlencode($string)."\0"; }
	public static function wrN($bytes, $value)
	{
		$value = (int)$value;
		$write = '';
		for ($i = 0; $i < $bytes; $i++)
		{
			$write = chr($value & 0xFF).$write;
			$value >>= 8;
		}
		return $write;
	}
}
