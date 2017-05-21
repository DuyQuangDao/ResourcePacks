<?php

/*
 *
 * __  __ _                               
 *|  \/  (_)              /\              
 *| \  / |_ _ __   ___   /  \   _ __ ___  
 *| |\/| | | '_ \ / _ \ / /\ \ | '__/ _ \ 
 *| |  | | | | | |  __// ____ \| | | (_) |
 *|_|  |_|_|_| |_|\___/_/    \_\_|  \___/                     
 *
 *
 *
*/

namespace QuangDepZai\resourcepacks;

class ResourcePackInfoEntry{
	protected $packId; //UUID
	protected $version;
	protected $packSize;

	public function __construct(string $packId, string $version, $packSize = 0){
		$this->packId = $packId;
		$this->version = $version;
		$this->packSize = $packSize;
	}

	public function getPackId() : string{
		return $this->packId;
	}

	public function getVersion() : string{
		return $this->version;
	}

	public function getPackSize(){
		return $this->packSize;
	}

}