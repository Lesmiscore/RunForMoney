<?php
namespace nao20010128nao;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;

use pocketmine\Player;

use pocketmine\math\Vector3;

use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

class PluginMain extends PluginBase implements Listener{
	private $csender;
	public $system;
	public $stats;
	public $state;
	public $skins;
	public function onEnable(){
		$this->state=0;
		$this->csender=new ConsoleCommandSender();
		$this->csender->sendMessage(TextFormat::GREEN."Loading...");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder(),755);
		}
		if(!file_exists($this->getDataFolder()."system.yml")){
			$this->csender->sendMessage(TextFormat::RED."An unrecovable error found!");
			$this->csender->sendMessage(TextFormat::RED."system.yml not found!");
			$this->csender->sendMessage(TextFormat::RED."Creating template...");
			$temp=array(
				"messages"=>array(
					"notJoined"=>"You are not joined the game."
					"uaHunter"=>"You are a hunter!"
					"uaEscaper"=>"You are a escaper!"
					),
				);
			yaml_emit_file($this->getDataFolder()."system.yml",$temp);
			$this->csender->sendMessage(TextFormat::RED."Starting stopping...");
			$this->getServer()->shutdown();
			return;
		}else{
			$this->system=yaml_parse_file($this->getDataFolder()."system.yml");
		}
		$this->csender->sendMessage(TextFormat::GREEN."Loading stats.yml...");
		if(file_exists($this->getDataFolder()."stats.yml")){
			$this->stats=yaml_parse_file($this->getDataFolder()."stats.yml");
		}else{
			$this->stats=array();
		}
		$this->csender->sendMessage(TextFormat::GREEN."Preparing some...");
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->register(
			"entry", 
			new EntryCommand($this, "entry", "Entry into the game.")
		);
		$commandMap->register(
			"stats", 
			new StatsCommand($this, "stats", "View your stats.")
		);
		$this->csender->sendMessage(TextFormat::GREEN."Done! Continuing enabling next plugins...");
	}
	public function onDisable(){
		yaml_emit_file($this->getDataFolder()."stats.yml",$this->stats);
	}
	public function onBlockPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		if($player->getGamemode()!==Player::CREATIVE){
			$event->setCancelled(true);
		}
	}
	public function onBlockBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if($player->getGamemode()!==Player::CREATIVE){
			$event->setCancelled(true);
		}
	}
	public function onPlayerQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		
	}
	public function onPlayerDeath(PlayerDeathEvent $event){
		$player = $event->getEntity();
		$username = $player->getName();
		$event->setKeepInventory(true);
		
	}
	public function onPlayerDamageBlock(EntityDamageByBlockEvent $event){
		$event->setCancelled(true);
	}
	public function onPlayerDamagePlayer(EntityDamageByEntityEvent $event){
		$event->setCancelled(true);
	}
	private function prepareStat($name){
		if(!array_key_exists($this->stats,mb_strtolower($name))){
			$this->stats=array_merge($this->stats,array(mb_strtolower($name)=>array(
				"catches"=>0,
				"caught"=>0,
				"becomeHunter"=>0,
				"becomeEscaper"=>0,
				)));
		}
		if(!array_key_exists($this->money,mb_strtolower($name))){
			$this->money=array_merge($this->money,array(mb_strtolower($name)=>0));
		}
	}
}