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

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use QuangDepZai\resourcepacks\ResourcePackInfoEntry;
use QuangDepZai\resourcepacks\ResourcePackManager;
use QuangDepZai\resourcepacks\ZippedResourcePack;

class ResourcePack extends PluginBase implements Listener {

	/** @var Server */
	private $server;

	/** @var string */
	private $path;

	/** @var Config */
	private $resourcePacksConfig;

	/** @var bool */
	private $serverForceResources = false;

	/** @var ResourcePack[] */
	private $resourcePacks = [];

	/** @var ResourcePack[] */
	private $uuidList = [];

	public function ResourcePack(Server $server, string $path){
		$this->server = $server;
		$this->path = $path;

		if(!file_exists($this->plugin->getDataFolder() . "resourcepack/")){
			$this->server->getLogger()->debug("材质包路径 $path 不存在, 创造文件夹中...");
			mkdir($this->plugin->getDataFolder() . "resourcepack/");
		}elseif(!is_dir($this->plugin->getDataFolder() . "resourcepack/")){
			throw new \InvalidArgumentException("材质包路径 $path 已经存在且不是一个文件夹");
		}

		if(!file_exists($this->plugin->getDataFolder() . "resourcepack/" . "resource_packs.yml")){
			file_put_contents($this->plugin->getDataFolder() . "resourcepack/" . "resource_packs.yml", file_get_contents("resource_packs.yml"));
		}

		$this->resourcePacksConfig = new Config($this->plugin->getDataFolder() . "resourcepack/" . "resource_packs.yml", Config::YAML, []);

		$this->serverForceResources = (bool) $this->resourcePacksConfig->get("force_resources", false);

		$this->server->getLogger()->info("Chạy plugin...");

		foreach($this->resourcePacksConfig->get("resource_stack", []) as $pos => $pack){
			try{
				$packPath = $this->plugin->getDataFolder() . "resourcepack/" . DIRECTORY_SEPARATOR . $pack;
				if(file_exists($packPath)){
					$newPack = null;
					//Detect the type of resource pack.
					if(is_dir($packPath)){
						$this->server->getLogger()->warning("文件夹的材质包 $pack 暂时不支持,请压缩");
					}else{
						$info = new \SplFileInfo($packPath);
						switch($info->getExtension()){
							case "zip":
								$newPack = new ZippedResourcePack($packPath);
								break;
							default:
								$this->server->getLogger()->warning("未知类型材质包 $pack 暂时未支持");
								break;
						}
					}

					if($newPack instanceof ResourcePack){
						$this->resourcePacks[] = $newPack;
						$this->uuidList[$newPack->getPackId()] = $newPack;
					}
				}else{
					$this->server->getLogger()->warning("找不到材质包 $pack");
				}
			}catch(\Throwable $e){
				$this->server->getLogger()->logException($e);
			}
		}

		$this->server->getLogger()->debug("成功加载 " . count($this->resourcePacks) . " 个材质包");
	}

	/**
	 * @return bool
	 */
	public function resourcePacksRequired() : bool{
		return $this->serverForceResources;
	}

	/**
	 * @return ResourcePack[]
	 */
	public function getResourceStack() : array{
		return $this->resourcePacks;
	}

	/**
	 * @param string $id
	 *
	 * @return ResourcePack|null
	 */
	public function getPackById(string $id){
		return $this->uuidList[$id] ?? null;
	}

	/**
	 * @return string[]
	 */
	public function getPackIdList() : array{
		return array_keys($this->uuidList);
	}
}