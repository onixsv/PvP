<?php
declare(strict_types=1);

namespace PvP;

use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class PvPPlugin extends PluginBase{

	/** @var Config */
	protected Config $config;

	protected array $db = [];

	protected function onEnable() : void{
		$this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, []);
		$this->db = $this->config->getAll();
	}

	protected function onDisable() : void{
		$this->config->setAll($this->db);
		$this->config->save();
	}

	public function getRandomSpawn() : Position{
		if(count($this->db) === 0){
			throw new \RuntimeException("PVP의 스폰이 설정되어있지 않습니다.");
		}

		shuffle($this->db);

		[$x, $y, $z, $world] = explode(":", $this->db[array_rand($this->db)]);

		return new Position((float) $x, (float) $y, (float) $z, Server::getInstance()->getWorldManager()->getWorldByName($world));
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($sender instanceof Player){
			if($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)){
				switch($args[0] ?? "x"){
					case "추가":
						$this->db[] = OnixUtils::posToStr($sender->getPosition());
						OnixUtils::message($sender, "추가되었습니다.");
						break;
					default:
						try{
							$spawn = $this->getRandomSpawn();
						}catch(\RuntimeException $e){
							OnixUtils::message($sender, $e->getMessage());
							return false;
						}
						$sender->teleport($spawn);
						$sender->getEffects()->clear();

						OnixUtils::message($sender, "PVP로 이동하였습니다.");
				}
			}else{
				try{
					$spawn = $this->getRandomSpawn();
				}catch(\RuntimeException $e){
					OnixUtils::message($sender, $e->getMessage());
					return false;
				}
				$sender->teleport($spawn);
				$sender->getEffects()->clear();

				OnixUtils::message($sender, "PVP로 이동하였습니다.");
			}
		}
		return true;
	}
}