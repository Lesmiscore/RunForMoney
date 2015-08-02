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

use pocketmine\entity\Effect;

use poscketmine\event\player\PlayerJoinEvent;
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
	public $session;
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
					"notJoined"=>TextFormat::RED."You are not joined the game."
					"uaHunter"=>TextFormat::RED."You are a hunter!"
					"uaEscaper"=>TextFormat::GREEN."You are a escaper!"
					),
				"lobby"=>array(
					"level"=>"world",
					"where"=>array(
							"x"=>0,
							"y"=>0,
							"z"=>0,
							),
					),
				"field"=>array(
					"world"=>array(
						"hunter"=>array(
							"x"=>0,
							"y"=>0,
							"z"=>0,
							),
						"escaper"=>array(
							"x"=>0,
							"y"=>0,
							"z"=>0,
						)
					)
				),
				"hunters"=>array(
					"hunterRatio"=>"0.2",
					),
				"game"=>array(
					"need"=>"15",
					),
			);
			yaml_emit_file($this->getDataFolder()."system.yml",$temp);
			$this->csender->sendMessage(TextFormat::RED."Stopping...");
			$this->getServer()->shutdown();
			return;
		}else{
			$this->system=yaml_parse_file($this->getDataFolder()."system.yml");
		}
		$this->csender->sendMessage(TextFormat::GREEN."Loading stats.db...");
		if(file_exists($file=$this->getDataFolder()."stats.yml")){
			$this->stats=\SQLite3($file,SQLITE3_OPEN_READWRITE);
		}else{
			$this->stats=\SQLite3($file,SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
		}
		$this->db_s("CREATE TABLE IF NOT EXISTS player(name TEXT PRIMARY KEY, catches INT, caught INT, becomeHunter INT, becomeEscaper INT)");
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
		$this->stats->close();
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
	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		$this->prepareStat($username);
		$player->setHealth(20);
		$player->addEffect(Effect::getEffect(1)->setDuration(10000)->setVisible(false)->setAmplifier(2));
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
		$name=strtolower($name);
		$stat=$this->db_s("SELECT ban, reason, stuff FROM player WHERE name = \"$name\"",true);
		if(empty($stat)){
			$this->db_s("INSERT OR REPLACE INTO player VALUES(\"$name\", \"0\", \"0\", \"0\", \"0\")");
		}
	}
	function db_s($sql, $return = false){
		if($return){
			return $this->stats->query($sql)->fetchArray();
		}else{
			$this->stats->query($sql);
			return true;
		}
	}
}