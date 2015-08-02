<?php
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

class GameSession implements Listener{
	/*Plugin*/
	private $plg;
	/*Game managing*/
	private $hunter,$escaper,$notjoin ,$system ,$isRunning;
	public $skins;
	
	public __construct(PluginMain $plugin){
		$this->plg=$plugin;
		$this->skins=array();
		$this->system=$plugin->system;
		$plugin->getServer()->getPluginManager()->registerEvents($plugin, $this);
	}
	public function onPlayerQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		
	}
	public function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		$this->skins=array_merge($this->skins,array(strtolower(username)=>array("skin"=>$player->getSkin(),"isslim"=>$player->isSlim())));
	}
	public function onPlayerDeath(PlayerDeathEvent $event){
		$player = $event->getEntity();
		$username = $player->getName();
		
	}
	public function start(){
		//$player->addEffect(Effect::getEffect(1)->setDuration(10000)->setVisible(false)->setAmplifier(2));
	}
	private function chooseHunters(){
		$allPlayers=$this->plg->getServer()->getOnlinePlayers();
	}
}