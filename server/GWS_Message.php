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
		printf("%s << %s\n", $this->user() ? $this->user()->displayName() : '???', $payload);
		return $this->from->send($payload);
	}
	
	public function replyBinary($command, $data='')
	{
		printf("%s << BIN\n", $this->user() ? $this->user()->displayName() : '???');
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
		return $this->replyBinary(0x0000, $this->write16($code));
	}
	
	##############
	### Reader ###
	##############
	public function readPayload() { return $this->data; }
	public function readJSON() { return json_encode($this->data); }
	public function read8($index=-1) { return $this->readN(1, $index); }
	public function read16($index=-1) { return $this->readN(2, $index); }
	public function read24($index=-1) { return $this->readN(3, $index); }
	public function read32($index=-1) { return $this->readN(4, $index); }
	public function readN($bytes, $index=-1)
	{
		$index = $this->index($index);
		$back = 0;
		for ($i = 0; $i < $bytes; $i++)
		{
			$back <<= 8;
			$back += ord($this->data[$index++]);
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
		$cmd = $this->read16();
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
	public function writeString($string) { return self::wrS($string); }
	public function write8($value) { return self::wrN(1, $value); }
	public function write16($value) { return self::wrN(2, $value); }
	public function write24($value) { return self::wrN(3, $value); }
	public function write32($value) { return self::wrN(4, $value); }
	public static function wr8($value) { return self::wrN(1, $value); }
	public static function wr16($value) { return self::wrN(2, $value); }
	public static function wr24($value) { return self::wrN(3, $value); }
	public static function wr32($value) { return self::wrN(4, $value); }
	public static function wrS($string) { return urlencode($string)."\0"; }
	public static function wrN($bytes, $value)
	{
		$write = '';
		for ($i = 0; $i < $bytes; $i++)
		{
			$write = chr($value & 0xFF).$write;
			$value >>= 8;
		}
		return $write;
	}
}
