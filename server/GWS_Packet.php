<?php
final class GWS_Packet
{
	private $data = [];
	public function __construct($command, $mid=GWS_Commands::DEFAULT_MID)
	{
		$this->addByte($command);
		$this->addLong($mid);
	}
	
	public function addByte($byte)
	{
		$data[] = $byte % 256;
	}

	public function addWord($word)
	{
		$this->addByte($word); $word >>= 8;
		$this->addByte($word);
	}

	public function addLong($long)
	{
		$this->addByte($long); $long >>= 8;
		$this->addByte($long); $long >>= 8;
		$this->addByte($long); $long >>= 8;
		$this->addByte($long);
	}

	public function addString($string)
	{
		return unpack('C*', utf8_encode($string));
	}
	
}