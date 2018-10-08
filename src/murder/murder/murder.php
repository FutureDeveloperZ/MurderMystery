<?php
/**
  * ___       _                  ___                 _                     ____
  *| __>_ _ _| |_ _ _  _ _  ___ | . \ ___  _ _  ___ | | ___  ___  ___  _ _|_  /
  *| _>| | | | | | | || '_>/ ._>| | |/ ._>| | |/ ._>| |/ . \| . \/ ._>| '_>/ / 
  *|_| `___| |_| `___||_|  \___.|___/\___.|__/ \___.|_|\___/|  _/\___.|_| /___|
  *                                                         |_|               
  *
  * → Creator: @Wolfkid20044
  * → Team: FutureDeveloperZ
  * → Link: http://github.com/FutureDeveloperZ
  *
*/

namespace murder\murder;
use pocketmine\level\sound\PopSound;
use pocketmine\Server;
use pocketmine\entity\Villager;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\tile\Chest;
use pocketmine\level\format\FullChunk;

use pocketmine\permission\Permission;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\block\Air;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat as TE;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\player\PlayerQuitEvent;
use murder\murder\ResetMap;
use pocketmine\math\Vector3;
use pocketmine\block\Block;


class murder extends PluginBase implements Listener {

       public $prefix = "§o§l§cMM:";
	public $mode = 0;
	public $arenas = array();
	public $vote = true;
	public $swop_voted;
	public $path;
	 
	public $currentLevel = "";
	  public $op = array();
	
	public function onEnable()
	{
		  $this->getLogger()->info(" By MCCreeperYT");
$this->path = $this->getDataFolder();
        @mkdir($this->path);
        if(!file_exists($this->path . "config.yml")) {
            $this->config = new Config($this->path . "config.yml", Config::YAML, array(
                "vote-count" => 1,
               
            ));
        } else {
            $this->getConfig()->save();
        }
            
        
                $this->getServer()->getPluginManager()->registerEvents($this ,$this);
              
		@mkdir($this->getDataFolder());
           
		
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
              
		if($config->get("arenas")!=null)
		{
			$this->arenas = $config->get("arenas");
		}
		foreach($this->arenas as $lev)
		{
			$this->getServer()->loadLevel($lev);
		}
		
		
		$config->save();
		
		$playerlang = new Config($this->getDataFolder() . "/languages.yml", Config::YAML);
		$playerlang->save();
		
		$lang = new Config($this->getDataFolder() . "/lang.yml", Config::YAML);
		if($lang->get("en")==null)
		{
			$messages = array();
			$messages["kill"] = " §o§2was murder ";
			$messages["cannotjoin"] = "§b§oYou can not Join.";
			$messages["seconds"] = "§oseconds to start";
			$messages["won"] = "§o§bWin §cMurder§b in the arena: §c";
			$messages["§f§o§b"] = "§b§oYou Join To The Game";
			$messages["Ase"] = "§f§o§bThe Assassin was";
			$messages["remainingminutes"] = "§ominutes left!";
			$messages["remainingseconds"] = "§oseconds left!";
			$messages["nowinner"] = "§o§cNo winners in Arena: §b";
			$messages["moreplayers"] = "§b§oMore players missing!";
			$lang->set("en",$messages);
		}
		$lang->save();
                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                $slots->save();
		$this->getScheduler()->scheduleRepeatingTask(new GameSender($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new RefreshSigns($this), 10);
		   
		    
	}
	
	public function onDeath(PlayerDeathEvent $event){
        $jugador = $event->getEntity();
        $mapa = $jugador->getLevel()->getFolderName();
        if(in_array($mapa,$this->arenas))
		{
                if($event->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent)
                {
                $asassin = $event->getEntity()->getLastDamageCause()->getDamager();
                if($asassin instanceof Player){
                $event->setDeathMessage("");
                foreach($jugador->getLevel()->getPlayers() as $pl){
				$playerlang = new Config($this->getDataFolder() . "/languages.yml", Config::YAML);
				$lang = new Config($this->getDataFolder() . "/lang.yml", Config::YAML);
				$toUse = $lang->get($playerlang->get($pl->getName()));
                                $muerto = $jugador->getNameTag();
                                $asesino = $asassin->getNameTag();
                                $pl->addTitle(" $muerto Kill");
                                
                                $pl->sendMessage(TE::WHITE. $muerto . TE::GREEN . " " . $toUse["kill"]);
 
			
			
                
                }
                }
          
                $jugador->setNameTag($jugador->getName());
                }
        }
        }
      
	
	public function onLog(PlayerLoginEvent $event)
	{
		$player = $event->getPlayer();
		$playerlang = new Config($this->getDataFolder() . "/languages.yml", Config::YAML);
		if($playerlang->get($player->getName())==null)
		{
			$playerlang->set($player->getName(),"en");
			$playerlang->save();
		}
                if(in_array($player->getLevel()->getFolderName(),$this->arenas))
		{
		$player->getInventory()->clearAll();
		$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
		$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
		$player->teleport($spawn,0,0);
                }
	}
	
        
        public function onQuit(PlayerQuitEvent $event)
        {
            $pl = $event->getPlayer();
            $level = $pl->getLevel()->getFolderName();
            if(in_array($level,$this->arenas))
            {
                $pl->removeAllEffects();
                $pl->getInventory()->clearAll();
                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                $pl->setNameTag($pl->getName());
                if($slots->get("slot1".$level)==$pl->getName())
                {
                    $slots->set("slot1".$level, 0);
                }
                if($slots->get("slot2".$level)==$pl->getName())
                {
                    $slots->set("slot2".$level, 0);
                }
                if($slots->get("slot3".$level)==$pl->getName())
                {
                    $slots->set("slot3".$level, 0);
                }
                if($slots->get("slot4".$level)==$pl->getName())
                {
                    $slots->set("slot4".$level, 0);
                }
                if($slots->get("slot5".$level)==$pl->getName())
                {
                    $slots->set("slot5".$level, 0);
                }
                if($slots->get("slot6".$level)==$pl->getName())
                {
                    $slots->set("slot6".$level, 0);
                }
                if($slots->get("slot7".$level)==$pl->getName())
                {
                    $slots->set("slot7".$level, 0);
                }
                if($slots->get("slot8".$level)==$pl->getName())
                {
                    $slots->set("slot8".$level, 0);
                }
                if($slots->get("slot9".$level)==$pl->getName())
                {
                    $slots->set("slot9".$level, 0);
                }
                if($slots->get("slot10".$level)==$pl->getName())
                {
                    $slots->set("slot10".$level, 0);
                }
                if($slots->get("slot11".$level)==$pl->getName())
                {
                    $slots->set("slot11".$level, 0);
                }
                if($slots->get("slot12".$level)==$pl->getName())
                {
                    $slots->set("slot12".$level, 0);                  
                }
                if($slots->get("slot13".$level)==$pl->getName())
                {
                    $slots->set("slot13".$level, 0);
                }
                if($slots->get("slot14".$level)==$pl->getName())
                {
                    $slots->set("slot14".$level, 0);
                }
                if($slots->get("slot15".$level)==$pl->getName())
                {
                    $slots->set("slot15".$level, 0);
                }
                if($slots->get("slot16".$level)==$pl->getName())
                {
                    $slots->set("slot16".$level, 0);
                }
                if($slots->get("slot17".$level)==$pl->getName())
                {
                    $slots->set("slot17".$level, 0);
                }
                if($slots->get("slot18".$level)==$pl->getName())
                {
                    $slots->set("slot18".$level, 0);
                }
                if($slots->get("slot19".$level)==$pl->getName())
                {
                    $slots->set("slot19".$level, 0);
                }
                if($slots->get("slot20".$level)==$pl->getName())
                {
                    $slots->set("slot20".$level, 0);
                }
            
               
                $slots->save();
            }
        }
          
	public function onBlockBr(BlockBreakEvent $event)
	{
            $player = $event->getPlayer();
            $level = $player->getLevel()->getFolderName();
            if(in_array($level,$this->arenas))
            {
                $event->setCancelled();
            }
	}
        
        public function onBlockPl(BlockPlaceEvent $event)
	{
            $player = $event->getPlayer();
            $level = $player->getLevel()->getFolderName();
            if(in_array($level,$this->arenas))
            {
                $event->setCancelled();
            }
	}
	 public function onDam(EntityDamageEvent $event) {
            $player = $event->getEntity();
            $level = $player->getLevel()->getFolderName();
            if(in_array($level,$this->arenas))
            {
            if ($event instanceof EntityDamageByEntityEvent) {
                if ($player instanceof Player && $event->getDamager() instanceof Player) {
             $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                if($config->get($level . "PlayTime") != null)
                {
                        if($config->get($level . "PlayTime") > 770)
                        {
                                $event->setCancelled();
                        }
                        elseif ((strpos($player->getNameTag(), "§f§o§b") !== false) && (strpos($event->getDamager()->getNameTag(), "§f§o§b") !== false)) {
                        $event->setCancelled();
                        }
                        elseif ((strpos($player->getNameTag(), "§c(Murder)") !== false) && (strpos($event->getDamager()->getNameTag(), "§c(Murder)") !== false)) {
                        $event->setCancelled();
                        }
                        else
                        {
                        $event->setKnockBack(0.2);
                        }
                }
                }
                }
            }
        }
	
	    
	
	
	public function onCommand(CommandSender $player, Command $cmd, $label, array $args):bool {
		$lang = new Config($this->getDataFolder() . "/lang.yml", Config::YAML);
        switch($cmd->getName()){
			case "mdr":
				if($player->isOp())
				{
					if(!empty($args[0]))
					{
						if($args[0]=="make")
						{
							if(!empty($args[1]))
							{
								if(file_exists($this->getServer()->getDataPath() . "/worlds/" . $args[1]))
								{
									$this->getServer()->loadLevel($args[1]);
									$this->getServer()->getLevelByName($args[1])->loadChunk($this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorX(), $this->getServer()->getLevelByName($args[1])->getSafeSpawn()->getFloorZ());
									array_push($this->arenas,$args[1]);
									$this->currentLevel = $args[1];
									$this->mode = 1;
									$player->sendMessage($this->prefix . "Touch sign to complete!");
									$player->setGamemode(1);
									$player->teleport($this->getServer()->getLevelByName($args[1])->getSafeSpawn(),0,0);
                                                                        $name = $args[1];
                                                                        $this->zipper($player, $name);
								}
								else
								{
									$player->sendMessage($this->prefix . "Dont found world");
								}
							}
							else
							{
								$player->sendMessage($this->prefix . "ERROR missing parameters.");
							}
						}
						else
						{
							$player->sendMessage($this->prefix . "Invalid command!");
						}
					}
					else
					{
					 $player->sendMessage("§a===§e>§cMurder Commands§e<§a===");
                                         $player->sendMessage("§b/mdr make [name]: Setup plugin");
                                         $player->sendMessage( "§b/mdrteam [team]!");
                                         $player->sendMessage("§b/mdrstart: Play");
                                         $player->sendMessage("§b/mdrlang: Select language");
					}
				}
				else
				{
				}
			return true;
			
			case "mdrlang":
				if(!empty($args[0]))
				{
					if($lang->get($args[0])!=null)
					{
						$playerlang = new Config($this->getDataFolder() . "/languages.yml", Config::YAML);
						$playerlang->set($player->getName(),$args[0]);
						$playerlang->save();
						$player->sendMessage(TE::GREEN . "New Lang: " . $args[0]);
					}
					else
					{
						$player->sendMessage(TE::RED . "Language No Extable");
					}
				}
			return true;
                        
                        case "mdrstart":
                            if($player->isOp())
				{
                                $player->sendMessage("§bStarting in 5 sec...");
                                $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                                $config->set("arenas",$this->arenas);
                                foreach($this->arenas as $arena)
                                {
                                        $config->set($arena . "PlayTime", 780);
                                        $config->set($arena . "StartTime", 5);
                                }
                                $config->save();
                                }
                                return true;
                                
                        case "mdrteam":
				  
					if(!($player->hasPermission("md.command.md") || $player->hasPermission("md.command.md"))) {
						$player->sendMessage("§4Solo §8[§6VIP§a+§8]");
						break;
						}
						
					$player->setNameTag("§f§o§b§b");
                 $player->sendMessage("§4You are Murder");
             
                                        
                                return true;
                                case "md":
                                if(!empty($args[0]))
					{
						if($args[0]=="md")
						{
                            if($player->hasPermission("blaze.vip")) {
                               
                                    if(isset($args[0])) {
                                           
                                           $player->sendMessage("§o§bPermission §6 Vip");
                                           $player->setNameTag("§f§o§b§b");
                                           }
                                           }
                                           }
                                            }
                                
                    return true;
                          
	}
        }
       
	public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$tile = $player->getLevel()->getTile($block);
		
		if($tile instanceof Sign) 
		{
			if($this->mode==26)
			{
				$tile->setText(TE::AQUA . "§a§l[§r§f§oJoin§r§a§l]§r§f",TE::YELLOW  . "0 / 20","§f§o§b" . $this->currentLevel,$this->prefix);
				$this->refreshArenas();
				$this->currentLevel = "";
				$this->mode = 0;
				$player->sendMessage($this->prefix . "Arena Registered!");
			}
			else
			{
				$text = $tile->getText();
				if($text[3] == $this->prefix)
				{
					if($text[0]==TE::AQUA . "§a§l[§r§f§oJoin§r§a§l]§r§f")
					{
						
						$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                                                $slots = new Config($this->getDataFolder() . "/slots.yml", Config::YAML);
                                                $namemap = str_replace("§f§o§b", "", $text[2]);
						$level = $this->getServer()->getLevelByName($namemap);
                                                if($slots->get("slot1".$namemap)==null)
                                                {

                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot1".$namemap, $player->getName());
                                                         $player->setNameTag("§3§f§o§3");
                                                          $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                         
                                                        $slots->save();
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . "§bjoin the game §6[§b1§6/§b20§6]");
                                                        }
                                                }
                                                else if($slots->get("slot2".$namemap)==null)
                                                {
                                              $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $slots->set("slot2".$namemap, $player->getName());
                                                        $thespawn = $config->get($namemap . "Spawn1");
                 $player->setNameTag("§f§o§b§b");
                                                        
                                                        $slots->save();
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bjoin the game §6[§b2§6/§b20§6]");
                                                        }
                                                
                                                }
                                                else if($slots->get("slot3".$namemap)==null)
                                                {
                                                	//detective
 $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot3".$namemap, $player->getName());
                                                        $player->setNameTag("§f§o§b");
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b3§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot4".$namemap)==null)
                                                {
                                                	$player->setNameTag("§f§o§b");
 $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot4".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b4§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot5".$namemap)==null)
                                                {
                                                	//murder
                                                $player->setNameTag("§f§o§b§b");
 $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot5".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b5§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot6".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
 $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot6".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b6§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot7".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
 $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot7".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b7§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot8".$namemap)==null)
                                                {
                                                     	$player->setNameTag("§f§o§b");
                                                	 $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot8".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b8§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot9".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                                $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot9".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b9§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot10".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot10".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b10§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot11".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot11".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b11§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot12".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot12".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b12§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot13".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot13".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b13§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot14".$namemap)==null)
                                                {
                                                	// 2 murder
                                                	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot14".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b14§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot15".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot15".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b15§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot16".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot16".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b16§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot17".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot17".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b17§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot18".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                    $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot18".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b18§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot19".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                                 $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot19".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b19§6/§b20§6]");
                                                        }
                                                        $slots->save();
                                                }
                                                else if($slots->get("slot20".$namemap)==null)
                                                {
                                                	             	$player->setNameTag("§f§o§b");
                             $player->getInventory()->setItem(8, Item::get(Item::BLAZE_ROD, 0, 1));
                                                        $thespawn = $config->get($namemap . "Spawn1");
                                                        $slots->set("slot20".$namemap, $player->getName());
                                                        
                                                        foreach($level->getPlayers() as $playersinarena)
                                                        {
                                                        $playersinarena->sendMessage($player->getName() . " §bI joined the game §6[§b20§6/§cFULL§6]");
                                                        }
                                                           $slots->save();
                                                        }
                                                                                               elseif ($text[0]==TE::DARK_PURPLE . "[Spectator]") {
                                            $namemap = str_replace("§f", "", $text[2]);
                                            $level = $this->getServer()->getLevelByName($namemap);
                                            $player->setGamemode(3);
                                            $player->teleport($level->getSafeSpawn(),0,0);
                                            $player->setNameTag("§o§b[Specter]§f" . $player->getName());
                                        }
                                                        
                                                
                                                
                                                $player->sendMessage($this->prefix. "§f§o§bJoined the Game");
                                                
                                               
                                                 
                                                   $player->setNameTagVisible(false);
                                                   
                                               
						$spawn = new Position($thespawn[0]+0.5,$thespawn[1],$thespawn[2]+0.5,$level);
						$level->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
						$player->teleport($spawn,0,0);
						$player->getInventory()->clearAll();
                                                $player->removeAllEffects();
                                                $player->setHealth(20);
                                                $player->setFood(20);
                                              if(strpos($player->getNameTag(), ".....") !== false)
                                                {
                                                    $player->setGamemode(0);
                                                    $player->getInventory()->clearAll();
                                                    $player->setNameTag("§c(Murder)".$player->getName());                             
                                                    $player->getInventory()->setItem(1, Item::get(Item::DIAMOND_SWORD, 0, 1));
                                                    $player->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 5));
                                                    $player->getInventory()->sendArmorContents($player);
                                                    $thespawn = $config->get($namemap . "Spawn1");
                                                }
                                                else
                                                {
                                                	$player->getInventory()->setItem(0, Item::get(Item::BLAZE_ROD, 0, 1));
                                                }
                                                
					}
					else
					{
						$playerlang = new Config($this->getDataFolder() . "/languages.yml", Config::YAML);
						$lang = new Config($this->getDataFolder() . "/lang.yml", Config::YAML);
						$toUse = $lang->get($playerlang->get($player->getName()));
						$player->sendMessage($this->prefix . $toUse["cannotjoin"]);
					}
				}
			}
		}
		else if($this->mode>=1&&$this->mode<=1)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn " . $this->mode . "Has been create!");
			$this->mode++;
			$config->save();
		}
		else if($this->mode==2)
		{
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
			$config->set($this->currentLevel . "Spawn" . $this->mode, array($block->getX(),$block->getY()+1,$block->getZ()));
			$player->sendMessage($this->prefix . "Spawn " . $this->mode . "Has been create!");
			$config->set("arenas",$this->arenas);
			$player->sendMessage($this->prefix . "Touch the sign for registration!");
			$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
			$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
			$player->teleport($spawn,0,0);
			$config->save();
			$this->mode=26;
		}
	}
	
   
   
   
   
   
	public function onInteraction(PlayerInteractEvent $e){
		
          if($e->getItem()->getId() == Item::BLAZE_ROD) {
            $o = $e->getPlayer();
                     	if(strpos($o->getNameTag(), "§f§o§b") !== false)
                {
                	$o->sendMessage("§4§oSolo user §6VIPs");
                }
         
   
  
                 $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		$config->set("arenas",$this->arenas);
		foreach($this->arenas as $arena)
		{
     $levelArena = $this->getServer()->getLevelByName($arena);
                            
				if($levelArena instanceof Level)
				{
					
					$playersArena = $levelArena->getPlayers();
                               
                      
                                    
                                            
                                                
                                                	
                                            $swop_count = $this->getConfig()->get("vote-count");
                                            
                                            $this->swop_voted = $this->swop_voted + 1;
                                            
                                            
                                         
                                            $this->vote = $this->vote = true;
                                              if(strpos($o->getNameTag(), "§f§o§b§b") !== false)
                                              {
                                            if($this->swop_voted >= $swop_count) {
            	
                                                        
                                                        
						
						  
			 
                 $o->setNameTag("§f§o§b§b" . $o->getName());
                 $o->sendMessage("§4You are Murder");
                  }
                 else
                   {
                 
                 $o->setNameTag("§3§f§o§3" . $o->getName());
                 $o->sendMessage("§bYou are Detective");
                 
                 
                 
                                   
                                                 
                                                    $this->swop_voted = 0;
                                                    $this->vote = true;
                                                    }
                                                    }
                                                   }
                                                   }
                                                  
                                                    }
                                                    }
                                                   
	
	public function refreshArenas()
	{
		$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
		$config->set("arenas",$this->arenas);
		foreach($this->arenas as $arena)
		{
			$config->set($arena . "PlayTime", 780);
			$config->set($arena . "StartTime", 40);
		}
		$config->save();
	}
        
        public function zipper($player, $name)
        {
        $path = realpath($player->getServer()->getDataPath() . 'worlds/' . $name);
				$zip = new \ZipArchive;
				@mkdir($this->getDataFolder() . 'arenas/', 0755);
				$zip->open($this->getDataFolder() . 'arenas/' . $name . '.zip', $zip::CREATE | $zip::OVERWRITE);
				$files = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($path),
					\RecursiveIteratorIterator::LEAVES_ONLY
				);
                                foreach ($files as $datos) {
					if (!$datos->isDir()) {
						$relativePath = $name . '/' . substr($datos, strlen($path) + 1);
						$zip->addFile($datos, $relativePath);
					}
				}
				$zip->close();
				$player->getServer()->loadLevel($name);
				unset($zip, $path, $files);
        }
}

class RefreshSigns extends murderTask {
    public $prefix = "§a§l[§r§f§oMurder§r§a§l]§r§f";
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
  
	public function onRun($tick)
	{
		$allplayers = $this->plugin->getServer()->getOnlinePlayers();
		$level = $this->plugin->getServer()->getDefaultLevel();
		$tiles = $level->getTiles();
		foreach($tiles as $t) {
			if($t instanceof Sign) {	
				$text = $t->getText();
				if($text[3]==$this->prefix)
				{
					$aop = 0;
                                        $namemap = str_replace("§f§o§b", "", $text[2]);
					foreach($allplayers as $player){if($player->getLevel()->getFolderName()==$namemap){$aop=$aop+1;}}
					$ingame = TE::AQUA . "§a§l[§r§f§oJoin§r§a§l]§r§f";
					$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
					if($config->get($namemap . "PlayTime")!=780)
					{
						$ingame = TE::DARK_PURPLE . "[Spectator]";
					}
					else if($aop>=16)
					{
						$ingame = TE::GRAY . "[".TE::GOLD."VIP".TE::GREEN."+".TE::GRAY."]";
					}
					$t->setText($ingame,TE::YELLOW  . $aop . " / 20",$text[2],$this->prefix);
				}
			}
		}
	}
}

class GameSender extends murderTask {
       public $prefix = "§a§l[§r§f§oMurder§r§a§l]§r§f";
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
	// panel
	

	 public function especter()
	{
		$level = $this->plugin->getServer()->getDefaultLevel();
		$tiles = $level->getTiles();
		foreach($tiles as $t) {
			if($t instanceof Sign) {	
				$text = $t->getText();
				if($text[3]==$this->prefix)
				{
                                    $config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
                                        $aop = 0;
                                        $namemap = $config->get("currentArena");
                                        foreach($this->plugin->getServer()->getLevelByName($namemap)->getPlayers() as $player)
                                        {
                                            if($player->getGamemode()===0)
                                            {
                                                $aop=$aop+1;
                                            }
                                        }
					$ingame = TE::AQUA . "§a§l[§r§f§oJoin§r§a§l]§r§f";
					if($config->get($namemap . "PlayTime")!=779)
					{
						$ingame = TE::DARK_GRAY . "[Spectator]";
					}
					elseif($aop>=16)
					{
						$ingame = TE::GRAY . "[".TE::GOLD."VIP".TE::GREEN."+".TE::GRAY."]";
					}
					$t->setText($ingame,TE::YELLOW  . $aop . " / 20","§f§o§b".$namemap,$this->prefix);
				}
			}
		}
	}
        
        public function getResetmap() {
        Return new ResetMap($this);
        }
  
	public function onRun($tick)
	{
		$config = new Config($this->plugin->getDataFolder() . "/config.yml", Config::YAML);
		$arenas = $config->get("arenas");

		if(!empty($arenas))
		{
			foreach($arenas as $arena)
			{
				$time = $config->get($arena . "PlayTime");
				$timeToStart = $config->get($arena . "StartTime");
				$levelArena = $this->plugin->getServer()->getLevelByName($arena);
				if($levelArena instanceof Level)
				{
					$playersArena = $levelArena->getPlayers();
					if(count($playersArena)==0)
					{
						$config->set($arena . "PlayTime", 780);
						$config->set($arena . "StartTime", 40);
					}
					else
					{
						if(count($playersArena)>=2)
						{
							if($timeToStart>0)
							{
								$timeToStart--;
								foreach($playersArena as $pl)
								{
									$playerlang = new Config($this->plugin->getDataFolder() . "/languages.yml", Config::YAML);
									$lang = new Config($this->plugin->getDataFolder() . "/lang.yml", Config::YAML);
									$toUse = $lang->get($playerlang->get($pl->getName()));
									$pl->sendPopup(TE::DARK_AQUA. $timeToStart . " " . $toUse["seconds"].TE::RESET);
								}
                                                                if($timeToStart==39)
                                                                {
                                                                	
                                                                    $levelArena->setTime(7000);
                                                                    $levelArena->stopTime();
                                                                }
                                                                 if($timeToStart<=5)
                                                                        {
                                                                        $levelArena->addSound(new PopSound($pl));
                                                                        }
                                                                    
                                                          
                                                          if($timeToStart<=39)
								{
								$time2 = $timeToStart + 1;
								
									 if($timeToStart == 30 || $timeToStart == 15 || $timeToStart == 10 || $timeToStart ==5 || $timeToStart ==4 || $timeToStart ==3 || $timeToStart ==2 || $timeToStart ==1)
									{
										foreach($playersArena as $pl)
										{
											
											$pl->addTitle("§b§o $timeToStart ");
										}
									}
									}
									
                                                             
                                                                       if($timeToStart<=1)
								                            {
									                          foreach($playersArena as $pl)
                                                                        {
                                                                        	if(strpos($pl->getNameTag(), "§f§o§b§b") !== false)
                                                                        {
                                                                        	$item = Item::get(Item::DIAMOND_SWORD, 0, 1);
                $item = Item::get(Item::DIAMOND_SWORD, 0, 1);
                $pl->setNameTag("§f§o§b§b");
                $pl->getInventory()->setItem(1, $item);
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->getInventory()->setItem(2, Item::get(Item::CLOCK, 0, 1));
                $pl->addTitle("§4Your the Killer");
                }
                }
                }
                                                                        
                                                                        
                                                                 if($timeToStart<=0)
								                            {
									                          foreach($playersArena as $pl)
                                                                        {
                                                                        	if(strpos($pl->getNameTag(), "§3§f§o§3") !== false)
                                                                        {
                                                                        	$item = Item::get(Item::WOODEN_SWORD, 0, 1);
                $pl->getInventory()->setItem(1, $item);
                $pl->setNameTag("§3§f§o§3");
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->addTitle("§bDetective");
                }
                }
                }
                  if($timeToStart<=0)
								                            {
									                          foreach($playersArena as $pl)
                                                                        {
                	if(strpos($pl->getNameTag(), "§f§o§b") !== false)
                {
                              $r = rand(1,7);
            switch($r){
                case 1:
                  $pl->setNameTag("§f§o§b");
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->addTitle("§bInnocents");
                break;
                case 2:
                  $pl->setNameTag("§f§o§b");
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->addTitle("§bInnocents");
                break;
                case 3:
                $item = Item::get(Item::WOODEN_SWORD, 0, 1);
                $pl->getInventory()->setItem(1, $item);
                $pl->setNameTag("§3§f§o§3");
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->addTitle("§bDetective");
                break;
                case 4:
             $pl->setNameTag("§f§o§b");
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->addTitle("§bInnocents");
                break;
                case 5:
                  $pl->setNameTag("§f§o§b");
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->addTitle("§bInnocents");
                break;
                case 6:
                  $pl->setNameTag("§f§o§b");
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->addTitle("§bInnocents");
                break;
                 case 7:
                $pl->setNameTag("§f§o§b");
                $pl->getInventory()->setItem(0, Item::get(Item::BREAD, 0, 1));
                $pl->addTitle("§bInnocents");
                break;
        	}
        }                                           	
                                                   
                         }                                               	
                                                                
                                                             
          }
                
                
                
                                                              	
                                                                            
								$config->set($arena . "StartTime", $timeToStart);
							}
							else
							{
                                                                $colors = array();
                                                                foreach($playersArena as $pl)
                                                                {
                                                                array_push($colors, $pl->getNameTag());
                                                                }
                                                                $names = implode("-", $colors);
                                                                $ase = substr_count($names, "§f§o§b§b");
                                                                $ino = substr_count($names, "§f§o§b");
                                                                $det = substr_count($names, "§3§f§o§3");
                                                                foreach($playersArena as $pla)
                                                                {
                                                                    if(strpos($pla->getNameTag(), "§f§o§b§b") !== false)
                                                                    {
                                                                   
                                                                    }
                                                                }
                                                                foreach($playersArena as $pla)
                                                                {
                                                                  
                                                                    if(strpos($pla->getNameTag(), "§f§o§b") !== false)
                                                                    {
                                                                   
                                                                    }
                                                                }
                                                                foreach($playersArena as $pla)
                                                                {
                                                                                                                                     if(strpos($pla->getNameTag(), "§3§f§o§3") !== false)
                                                                    {
                                                                   $player = $pla->getPlayer()->getName();
                                                                   $pla->sendPopup("§o§b $player: Innocents");
                                                                        $pla->sendTip(TE::RED."Murder:" . $ase .TE::AQUA. " Innocents:" . $ino .TE::AQUA. " Detective:" . $det);
                                                                        
                                                                    }
                                                                    else
                                                                    {
                                                                    	  $player = $pla->getPlayer()->getName();
                                                                   $pla->sendPopup("§o§b $player: §4Murdere");
                                                                            $pla->sendTip(TE::RED."Murder:" . $ase .TE::AQUA. " Innocents:" . $ino .TE::AQUA. " Detective:" . $det);
                                                                 }
                                                                    }
                                                                    
                                                                $winner = null;
                                                                $winners = array();
                                                                if($ase!=0 && $ino==0)
                                                                {
                                                                    $winner = TE::RED."§oMurderer".TE::GRAY." I win Murder in ";
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                    	                               $pl->setNameTagVisible(true);
                                                                        if(strpos($pl->getNameTag(), "§f§o§b§b") !== false)
                                                                        {
                                                                            array_push($winners, $pl->getNameTag());
                                                                        }
                                                                    }
                                                                }
                                                                if($ase==0 && $ino!=0)
                                                                {
                                                                    $winner = TE::AQUA."§oInocentes".TE::GRAY." They won Murder in ";
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                        if(strpos($pl->getNameTag(), "§f§o§b") !== false)
                                                                        {
                                                                            array_push($winners, $pl->getNameTag());
                                                                        }
                                                                    }
                                                                }
                                                                if($winner!=null)
                                                                {
                                                                    $this->plugin->getServer()->broadcastMessage($this->prefix .TE::YELLOW. ">> ".$winner.TE::AQUA.$arena);
                                                                    $namewin = implode(", ", $winners);
                                                                    $this->plugin->getServer()->broadcastMessage($this->prefix .TE::YELLOW. ">> ".TE::AQUA."Winners(es):".$namewin);
                                                                    foreach($playersArena as $pl)
                                                                    {
                                                                    	 
                        
                                                                        $pl->getInventory()->clearAll();
                                                                        $pl->sendMessage("§oThe Murder was §4Murder xd");
                                                                        $pl->removeAllEffects();
                                                                          $pl->setNameTagVisible(true);
                                                                        $pl->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
                                                                        $pl->setNameTag($pl->getName());
                                                                     
                                                                        $config->set($arena . "PlayTime", 780);
                                                                        $config->set($arena . "StartTime", 30);
                                                                     
                                                                        $config->save();
                                                                    }
                                                                }
								$time--;
								
								
                      
                       


								
									
	
		
		
						
						
								if($time == 778)
								{ 
									
                                                                        $slots = new Config($this->plugin->getDataFolder() . "/slots.yml", Config::YAML);
                                                                        $slots->set("slot1".$arena, 0);
                                                                        $slots->set("slot2".$arena, 0);
                                                                        $slots->set("slot3".$arena, 0);
                                                                        $slots->set("slot4".$arena, 0);
                                                                        $slots->set("slot5".$arena, 0);
                                                                        $slots->set("slot6".$arena, 0);
                                                                        $slots->set("slot7".$arena, 0);
                                                                        $slots->set("slot8".$arena, 0);
                                                                        $slots->set("slot9".$arena, 0);
                                                                        $slots->set("slot10".$arena, 0);
                                                                        $slots->set("slot11".$arena, 0);
                                                                        $slots->set("slot12".$arena, 0);
                                                                        $slots->set("slot13".$arena, 0);
                                                                        $slots->set("slot14".$arena, 0);
                                                                        $slots->set("slot15".$arena, 0);
                                                                        $slots->set("slot16".$arena, 0);
                                                                        $slots->set("slot17".$arena, 0);
                                                                        $slots->set("slot18".$arena, 0);
                                                                        $slots->set("slot19".$arena, 0);
                                                                        $slots->set("slot20".$arena, 0);
                                                                        $slots->save();
									foreach($playersArena as $pl)
									{
										    
									
										  $tiles = $levelArena->getTiles();
										
                                                                    foreach ($tiles as $tile) {
                                                                        if ($tile instanceof Sign) {
                                                                            $text = $tile->getText();
                                                                            if (strtolower($text[0]) == "diamond") {
                                                                           $levelArena->dropItem(new Vector3($tile->getX() + 0.5, $tile->getY() + 2, $tile->getZ() + 0.5), Item::get(Item::DIAMOND, 0, 1));
                                                                            }
                                                                        }
                                                                    }
										
										$pl->sendMessage("§f§o§b>---------------------");
                                                                                $pl->sendMessage("§f§o§b>§c¡§bThe Game just Started!");
                                                                                $pl->sendMessage("§f§o§b>§cMap: §b" . $arena);
                                                                                $pl->sendMessage("§f§o§b>Get 5 Diamonds for a Sword?");
                                                                                $pl->sendMessage("§f§o§b>--------------------");
									}
								}
                                              if($time == 660)
								{
									foreach($playersArena as $pl)
									{
										     
									
										$tiles = $levelArena->getTiles();
										
                                                                    foreach ($tiles as $tile) {
                                                                        if ($tile instanceof Sign) {
                                                                            $text = $tile->getText();
                                                                            if (strtolower($text[0]) == "diamond") {
                                                                           $levelArena->dropItem(new Vector3($tile->getX() + 0.5, $tile->getY() + 2, $tile->getZ() + 0.5), Item::get(Item::DIAMOND, 0, 1));
                                                                            }
                                                                        }
                                                                    }
								}
								}
								    if($time == 560)
								{
									foreach($playersArena as $pl)
									{
										     
									
										$tiles = $levelArena->getTiles();
										
                                                                    foreach ($tiles as $tile) {
                                                                        if ($tile instanceof Sign) {
                                                                            $text = $tile->getText();
                                                                            if (strtolower($text[0]) == "diamond") {
                                                                           $levelArena->dropItem(new Vector3($tile->getX() + 0.5, $tile->getY() + 2, $tile->getZ() + 0.5), Item::get(Item::DIAMOND, 0, 1));
                                                                            }
                                                                        }
                                                                    }
								}
								}
								
                                                                if($time == 550)
								{
									foreach($playersArena as $pl)
									{
										     
									
										$tiles = $levelArena->getTiles();
										
                                                                    foreach ($tiles as $tile) {
                                                                        if ($tile instanceof Sign) {
                                                                            $text = $tile->getText();
                                                                            if (strtolower($text[0]) == "diamond") {
                                                                           $levelArena->dropItem(new Vector3($tile->getX() + 0.5, $tile->getY() + 2, $tile->getZ() + 0.5), Item::get(Item::DIAMOND, 0, 1));
                                                                            }
                                                                        }
                                                                    }
										
										$pl->sendMessage("§b>--------------");
                                                                                $pl->sendMessage("§f§o§b>>§cMake by §b[TheGrittlex] Beta");
                                                                                $pl->sendMessage("§b>----------------");
									}
								}
								
								    if($time == 450)
								{
									foreach($playersArena as $pl)
									{
										     
									
										$pl->getInventory()->setItem(4, Item::get(Item::DIAMOND, 0, 1));                     
								}
								}
								    if($time == 400)
								{
									foreach($playersArena as $pl)
									{
										     
									
										$tiles = $levelArena->getTiles();
										
                                                                    foreach ($tiles as $tile) {
                                                                        if ($tile instanceof Sign) {
                                                                            $text = $tile->getText();
                                                                            if (strtolower($text[0]) == "diamond") {
                                                                           $levelArena->dropItem(new Vector3($tile->getX() + 0.5, $tile->getY() + 2, $tile->getZ() + 0.5), Item::get(Item::DIAMOND, 0, 1));
                                                                            }
                                                                        }
                                                                    }
								}
								}
                                                                
								if($time>=300)
								{
								$time2 = $time - 180;
								$minutes = $time2 / 60;
								}
								else
								{
									$minutes = $time / 60;
									if(is_int($minutes) && $minutes>0)
									{
										foreach($playersArena as $pl)
										{
											$playerlang = new Config($this->plugin->getDataFolder() . "/languages.yml", Config::YAML);
                                                                                        $lang = new Config($this->plugin->getDataFolder() . "/lang.yml", Config::YAML);
											$toUse = $lang->get($playerlang->get($pl->getName()));
											$pl->sendMessage($this->prefix . $minutes . " " . $toUse["remainingminutes"]);
										}
									}
									else if($time == 30 || $time == 15 || $time == 10 || $time ==5 || $time ==4 || $time ==3 || $time ==2 || $time ==1)
									{
										foreach($playersArena as $pl)
										{
											$playerlang = new Config($this->plugin->getDataFolder() . "/languages.yml", Config::YAML);
                                                                                        $lang = new Config($this->plugin->getDataFolder() . "/lang.yml", Config::YAML);
											$toUse = $lang->get($playerlang->get($pl->getName()));
											$pl->sendMessage($this->prefix . $time . " " . $toUse["remainingseconds"]);
										}
									}
									if($time <= 0)
									{
										foreach($playersArena as $pl)
										{
											$pl->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn(),0,0);
											$playerlang = new Config($this->plugin->getDataFolder() . "/languages.yml", Config::YAML);
                                                                                        $lang = new Config($this->plugin->getDataFolder() . "/lang.yml", Config::YAML);
											$toUse = $lang->get($playerlang->get($pl->getName()));
											$pl->sendMessage($this->prefix . $toUse["nowinner"].$arena);
											$pl->getInventory()->clearAll();
                                                                                        $pl->removeAllEffects();
                                                                                        $pl->setFood(20);
                                                                                        $pl->setHealth(20);
                                                                                        $player->setNameTagVisible(true);
                                                                                        $pl->setNameTag($pl->getName());
                                                                                        $this->getResetmap()->reload($levelArena);
										}
										$time = 780;
									}
								}
								$config->set($arena . "PlayTime", $time);
							}
						}
						else
						{
							if($timeToStart<=0)
							{
								foreach($playersArena as $pl)
								{
									$this->getOwner()->getServer()->broadcastMessage($this->prefix.$pl->getNameTag()." §b>>§cGano Murder en la arena: §b".$arena);
									$pl->teleport($this->getOwner()->getServer()->getDefaultLevel()->getSafeSpawn(),0,0);
									$pl->getInventory()->clearAll();
									
                                                                        $pl->removeAllEffects();
                                                                        $pl->setHealth(20);
                                                                        $pl->setFood(20);
                                                                         $pl->setNameTagVisible(true);
                                                                        $pl->setNameTag($pl->getName());
                                                                      
                                                                        $this->getResetmap()->reload($levelArena);
								}
								$config->set($arena . "PlayTime", 780);
								$config->set($arena . "StartTime", 30);
							}
							else
							{
								foreach($playersArena as $pl)
								{
									$playerlang = new Config($this->plugin->getDataFolder() . "/languages.yml", Config::YAML);
									$lang = new Config($this->plugin->getDataFolder() . "/lang.yml", Config::YAML);
									$toUse = $lang->get($playerlang->get($pl->getName()));
									$pl->sendPopup(TE::DARK_AQUA . $toUse["moreplayers"].TE::RESET);
									  
									$pl->sendTip("§b§oPlugin in Beta");
								}
								$config->set($arena . "PlayTime", 780);
								$config->set($arena . "StartTime", 40);
							}
						}
					}
				}
			}
		}

		$config->save();
	}
	
	
	}
