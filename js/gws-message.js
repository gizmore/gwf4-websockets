function GWS_Message(buffer) {
	//////////
	// Init //
	//////////
	this.SYNC = 0; 
	this.INDEX = 0;
	this.LENGTH = 0;
	if (buffer) {
		this.BUFFER = new Uint8Array(buffer);
		this.LENGTH = buffer.byteLength;
	}
	else {
		this.BUFFER = [];
	}
	
	/////////////////////
	// Setter / Getter //
	/////////////////////
	this.isSync = function() { return this.LENGTH > 0 ? (this.BUFFER[0] & 0x80) > 0 : false; };
	this.index = function(index) { if (index !== undefined) this.INDEX = index; return this.INDEX; };
	this.binaryBuffer = function()
	{
		var len = this.BUFFER.length;
		var buff = new Uint8Array(len);
		for (var i = 0; i < len; i++) {
			buff[i] = this.BUFFER[i];
		}
		return buff.buffer;
	};

	////////////
	// Reader //
	////////////
	this.read8 = function(index) { return this.readN(1, index); };
	this.read16 = function(index) { return this.readN(2, index); };
	this.read24 = function(index) { return this.readN(3, index); };
	this.read32 = function(index) { return this.readN(4, index); };
	this.readN = function(bytes, index) {
		index = index === undefined ? this.INDEX : index;
		var back = 0;
		for (var i = 0; i < bytes; i++) {
			back <<= 8;
			back |= this.BUFFER[index++];
		}
		this.INDEX = index;
		return back;
	};
	this.readString = function(index) {
		this.index(index);
		var back = '';
		while (code = this.read8()) {
			back += String.fromCharCode(code);
		}
		return decodeURIComponent(back);
	};
	this.readCmd = function() { return this.read16() & 0x7FFF; }
	this.readMid = function() { return this.read24(); };

	////////////
	// Writer //
	////////////
	this.write8 = function(value, index) { return this.writeN(1, value, index); };
	this.write16 = function(value, index) { return this.writeN(2, value, index); };
	this.write24 = function(value, index) { return this.writeN(3, value, index); };
	this.write32 = function(value, index) { return this.writeN(4, value, index); };
	this.writeN = function(bytes, value, index) {
		index = index === undefined ? this.INDEX : index;
		var jindex = index + bytes - 1;
		for (var i = 0; i < bytes; i++) {
			this.BUFFER[jindex--] = value & 0xFF;
			index++;
			value >>= 8;
		}
		this.LENGTH = this.BUFFER.length;
		this.INDEX = index;
		return this;
	};
	this.writeString = function(string, index) {
		var s = encodeURIComponent(string);
		var len = s.length, i = 0;
		while (i < len) {
			write8(s.charCodeAt(i++));
		}
		return write8(0);
	};
	this.cmd = function(cmd) {
		return this.write16(cmd);
	};
	this.async = function() {
		this.SYNC = 0;
		return this;
	};
	this.sync = function() {
		this.BUFFER[0] |= 0x80;
		this.SYNC = GWS_Message.nextMid();
		return this.write24(this.SYNC);
	};
	
	///////////
	// Debug //
	///////////
	this.dump = function() {
		var dump = '', i = 0;
		while (i < this.LENGTH) {
			dump += sprintf(' %02X', this.BUFFER[i++]);
		}
		return dump;
	};

	// yeah
	return this;
}

/////////////
// Factory //
/////////////
GWS_Message.NEXT_MID = 1;
GWS_Message.nextMid = function() {
	return GWS_Message.NEXT_MID++;
}
