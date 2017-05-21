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


class ZippedResourcePack{

	public static function verifyManifest(\stdClass $manifest){
		if(!isset($manifest->format_version) or !isset($manifest->header) or !isset($manifest->modules)){
			return false;
		}
		return
			isset($manifest->header->description) and
			isset($manifest->header->name) and
			isset($manifest->header->uuid) and
			isset($manifest->header->version) and
			count($manifest->header->version) === 3;
	}

	/** @var string */
	protected $path;

	/** @var \stdClass */
	protected $manifest;

	/** @var string */
	protected $sha256 = null;


	public function __construct(string $zipPath){
		$this->path = $zipPath;

		if(!file_exists($zipPath)){
			throw new \InvalidArgumentException("无法打开材质包 $zipPath: 文件夹无法打开");
		}

		$archive = new \ZipArchive();
		if(($openResult = $archive->open($zipPath)) !== true){
			throw new \InvalidStateException("打开 $zipPath时遇到ZipArchive错误 $openResult");
		}

		if(($manifestData = $archive->getFromName("manifest.json")) === false){
			throw new \InvalidStateException("无法加载材质包 $zipPath: 找不到主类");
		}

		$archive->close();

		$manifest = json_decode($manifestData);
		if(!self::verifyManifest($manifest)){
			throw new \InvalidStateException("无法加载材质包 $zipPath: 主类错误或不完整");
		}

		$this->manifest = $manifest;
	}

	public function getPackName() : string{
		return $this->manifest->header->name;
	}

	public function getPackVersion() : string{
		return implode(".", $this->manifest->header->version);
	}

	public function getPackId() : string{
		return $this->manifest->header->uuid;
	}

	public function getPackSize() : int{
		return filesize($this->path);
	}

	public function getSha256(bool $cached = true) : string{
		if($this->sha256 === null or !$cached){
			$this->sha256 = openssl_digest(file_get_contents($this->path), "sha256", true);
		}
		return $this->sha256;
	}

	public function getPackChunk(int $start, int $length) : string{
		return substr(file_get_contents($this->path), $start, $length);
	}
}