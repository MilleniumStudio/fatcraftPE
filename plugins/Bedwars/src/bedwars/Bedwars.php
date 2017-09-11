<?php

namespace Bedwars;

use pocketmine\block\Block;
use pocketmine\Command\Command;
use pocketmine\Command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\tile\Chest;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Bedwars\Merchant;

include("Merchant.php");

class Bedwars extends PluginBase implements Listener
{

    public $prefix = TextFormat::GRAY . "[" . TextFormat::DARK_AQUA . "Bedwars" . TextFormat::GRAY . "]" . TextFormat::WHITE . " ";
    public $registerSign = false;
    public $registerSignWHO = "";
    public $registerSignArena = "Arena1";
    public $registerBed = false;
    public $registerBedWHO = "";
    public $registerBedArena = "Arena1";
    public $registerBedTeam = "WHITE";
    public $mode = 0;
    public $arena = "Arena1";
    public $lasthit = array();
    public $pickup = array();
    public $isShopping = array();
    public $breakableblocks = array();

    // new vars
    public $currentState = STATE::IDLE;
    public $maxTimeWaiting = 30 * 20;
    public $teams;
    public $playerPerTeam;
    public $maxPlayer, $minPlayer;
    public $shopContent;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix . TextFormat::GREEN . "Plugin Bedwars enabled !");
        @mkdir($this->getDataFolder());

        $this->prepareConfig();

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new OnTick($this), 1);
    }

    public function prepareConfig()
    {
        // a bit of config
        $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if (empty($cfg->get("GameTimer"))) {
            $cfg->set("GameTimer", 30 * 60 + 1);
            $cfg->save();
        }
        if (empty($cfg->get("EndTimer"))) {
            $cfg->set("EndTimer", 16);
            $cfg->save();
        }
        if (empty($cfg->get("BreakableBlocks"))) {
            $cfg->set("BreakableBlocks", array(Item::HARDENED_CLAY, Item::END_STONE, Item::GLASS, Item::CHEST));
            $cfg->save();
        }
        $this->breakableblocks = $cfg->get("BreakableBlocks");
        $shop = new Config($this->getDataFolder() . "shop.yml", Config::YAML);

        $this->shopContent = array(
            Item::WOODEN_SWORD =>
                array(
                    array(
                        Item::STICK, 1, 384, 8
                    ),
                    array(
                        Item::STONE_SWORD, 1, 384, 20
                    ),
                    array(
                        Item::IRON_SWORD, 1, 384, 40
                    ),
                    array(
                        Item::DIAMOND_SWORD, 1, 384, 40
                    )
                ),
            Item::HARDENED_CLAY =>
                array(
                    array(
                        Item::HARDENED_CLAY, 4, 384, 1
                    ),
                    array(
                        Item::END_STONE, 4, 384, 1
                    ),
                    array(
                        Item::GLASS, 6, 384, 1
                    )
                ),
            Item::IRON_PICKAXE =>
                array(
                    array(
                        Item::STONE_PICKAXE, 4, 384, 1
                    ),
                    array(
                        Item::IRON_PICKAXE, 4, 384, 1
                    ),
                    array(
                        Item::DIAMOND_PICKAXE, 6, 384, 1
                    )
                ),
            Item::CHEST =>
                array(
                    array(
                        Item::SNOWBALL, 1, 384, 2
                    ),
                    array(
                        Item::LADDER, 1, 384, 4
                    ),
                    array(
                        Item::WEB, 1, 384, 2
                    ),
                    array(
                        Item::CHEST, 1, 384, 8
                    )
                ),
            Item::LEATHER_TUNIC =>
                array(
                    array(
                        Item::LEATHER_BOOTS, 1, 384, 2
                    ),
                    array(
                        Item::LEATHER_HELMET, 1, 384, 8
                    ),
                    array(
                        Item::CHAIN_CHESTPLATE, 1, 384, 20
                    ),
                    array(
                        Item::IRON_CHESTPLATE, 1, 384, 20
                    ),
                    array(
                        Item::DIAMOND_CHESTPLATE, 1, 384, 20
                    )
                )
        );


        $this->teams = (int)$cfg->get("Teams");
        $this->playerPerTeam = (int)$cfg->get("PlayersPerTeam");
    }


    ###################################    ===[EVENTS]===     ##################################################

    public function onTransaction(InventoryTransactionEvent $event)
    {
        $transactions = $event->getTransaction()->getTransactions();
        $inventories = $event->getTransaction()->getInventories();

        $player = null;
        $chestBlock = null;

        foreach ($transactions as $t) {
            foreach ($inventories as $inventory) {
                $chest = $inventory->getHolder();

                if ($chest instanceof Chest) {
                    $chestBlock = $chest->getBlock();
                    $transaction = $t;
                }
                if ($chest instanceof Player) {
                    $player = $chest;
                }
            }
        }
        if ($player != null && $chestBlock != null && isset($transaction)) {


            $config = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
            $all = $config->get("Shop");

            /*
            if(in_array($transaction->getTargetItem()->getId(), $all)){
                $this->isShopping[$player->getName()] = "ja";
            }
            */

            $chestTile = $player->getLevel()->getTile($chestBlock);
            if ($chestTile instanceof Chest) {
                $TargetItemID = $transaction->getTargetItem()->getId();
                $TargetItemDamage = $transaction->getTargetItem()->getDamage();
                $TargetItem = $transaction->getTargetItem();
                $inventoryTrans = $chestTile->getInventory();


                if (!$this->isShopping[$player->getName()]) {
                    $zahl = 0;
                    for ($i = 0; $i < count($all); $i += 2) {
                        if ($TargetItemID == $all[$i]) {
                            $zahl++;
                        }
                    }
                    if ($zahl == count($all)) {
                        $this->isShopping[$player->getName()] = true;
                    }
                }
                if (!$this->isShopping[$player->getName()]) {
                    $secondslot = $inventoryTrans->getItem(1)->getId();
                    if ($secondslot == 384) {
                        $this->isShopping[$player->getName()] = true;
                    }
                }

                if ($this->isShopping[$player->getName()]) {
                    if ($TargetItemID == Item::WOOL && $TargetItemDamage == 14) {
                        $event->setCancelled(true);
                        $config = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
                        $all = $config->get("Shop");
                        $chestTile->getInventory()->clearAll();
                        for ($i = 0; $i < count($all); $i = $i + 2) {
                            $slot = $i / 2;
                            $chestTile->getInventory()->setItem($slot, Item::get($all[$i], 0, 1));
                        }
                    }

                    $TransactionSlot = 0;
                    for ($i = 0; $i < $inventoryTrans->getSize(); $i++) {
                        if ($inventoryTrans->getItem($i)->getId() == $TargetItemID) {
                            $TransactionSlot = $i;
                            break;
                        }
                    }
                    $secondslot = $inventoryTrans->getItem(1)->getId();
                    if ($TransactionSlot % 2 != 0 && $secondslot == 384) {
                        $event->setCancelled(true);
                    }
                    if ($TargetItemID == 384) {
                        $event->setCancelled(true);
                    }
                    if ($TransactionSlot % 2 == 0 && ($secondslot == 384)) {
                        $Kosten = $inventoryTrans->getItem($TransactionSlot + 1)->getCount();

                        $yourmoney = $player->getXpLevel();

                        if ($yourmoney >= $Kosten) {
                            $money = $yourmoney - $Kosten;
                            $player->setXpLevel($money);
                            $player->getInventory()->addItem(Item::get($inventoryTrans->getItem($TransactionSlot)->getId(), $inventoryTrans->getItem($TransactionSlot)->getDamage(), $inventoryTrans->getItem($TransactionSlot)->getCount()));
                        }
                        $event->setCancelled(true);
                    }
                    if ($secondslot != 384) {
                        $event->setCancelled(true);
                        $config = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
                        $all = $config->get("Shop");
                        for ($i = 0; $i < count($all); $i += 2) {
                            if ($TargetItemID == $all[$i]) {
                                $chestTile->getInventory()->clearAll();
                                $suball = $all[$i + 1];
                                $slot = 0;
                                for ($j = 0; $j < count($suball); $j++) {
                                    $chestTile->getInventory()->setItem($slot, Item::get($suball[$j][0], 0, $suball[$j][1]));
                                    $slot++;
                                    $chestTile->getInventory()->setItem($slot, Item::get($suball[$j][2], 0, $suball[$j][3]));
                                    $slot++;
                                }
                                break;
                            }
                        }
                        $chestTile->getInventory()->setItem($chestTile->getInventory()->getSize() - 1, Item::get(Item::WOOL, 14, 1));
                    }
                }
            }

        }
    }

    public function onItemDrop(PlayerDropItemEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $item = $event->getItem();

        if ($item->getId() == Item::WOOL) {
//            $arena = $this->getArena();
            $team = $this->getTeamByBlockDamage($item->getDamage());
            $event->setCancelled();

            if (false /*$this->getArenaStatus($arena) == "Lobby"*/) { //todo check, I think that the player can only choose a team if he's on a lobby...
                if ($team != $this->getTeam($player->getNameTag())) {
                    if (in_array($team, $this->getAvailableTeams())) {
                        $player->setNameTag($this->getTeamColor($team) . $name);
                        $player->sendMessage($this->prefix . "Vous avez rejoins l'équipe " . TextFormat::GOLD . $team);
                        $player->getInventory()->removeItem($item);
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->sendMessage($this->prefix . "L'équipe " . TextFormat::GOLD . $team . TextFormat::WHITE . " est déjà pleine !");
                        $player->getInventory()->removeItem($item);
                        $player->getInventory()->addItem($item);
                    }
                } else {
                    $player->sendMessage($this->prefix . "Vous êtes déjà en équipe " . TextFormat::GOLD . $team);
                    $player->getInventory()->removeItem($item);
                    $player->getInventory()->addItem($item);
                }
            }

        }
    }

    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();

//        $arena = $this->getArena($player);
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $team = $this->getTeam($player->getNameTag());
        $players = $this->getPlayers();
        $status = $config->get("Status");
        $msg = $event->getMessage();
        $words = explode(" ", $msg);

        if (false /*$status == "Lobby"*/) {
            $event->setCancelled();
            foreach ($players as $pn) {
                $p = $this->getServer()->getPlayerExact($pn);
                if ($p != null) {
                    $p->sendMessage($name . " >> " . $msg);
                }
            }
        } else {
            if ($words[0] === "@a" or $words[0] === "@all") {
                array_shift($words);
                $msg = implode(" ", $words);
                $event->setCancelled();
                foreach ($players as $pn) {
                    $p = $this->getServer()->getPlayerExact($pn);
                    if ($p != null) {
                        $p->sendMessage(TextFormat::GRAY . "[" . TextFormat::GREEN . "ALL" . TextFormat::GRAY . "] " . $player->getNameTag() . TextFormat::GRAY . " >> " . TextFormat::WHITE . $msg);
                    }
                }
            } else {
                $event->setCancelled();
                foreach ($players as $pn) {
                    $p = $this->getServer()->getPlayerExact($pn);
                    if ($p != null) {
                        if ($this->getTeam($p->getNameTag()) == $this->getTeam($player->getNameTag())) {
                            //teamchat
                            $p->sendMessage(TextFormat::GRAY . "[" . $this->getTeamColor($this->getTeam($player->getNameTag())) . "Team" . TextFormat::GRAY . "] " . $player->getNameTag() . TextFormat::GRAY . " >> " . TextFormat::WHITE . $msg);
                        }
                    }
                }
            }
        }

    }

    public function onInvClose(InventoryCloseEvent $event)
    {
        $inventory = $event->getInventory();
        if ($inventory instanceof ChestInventory) {
            $config = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
            $all = $config->get("Shop");
            $realChest = $inventory->getHolder();
            $first = $all[0];
            $second = $all[2];
            if (($inventory->getItem(0)->getId() == $first && $inventory->getItem(1)->getId() == $second) || $inventory->getItem(1)->getId() == 384) {
                $event->getPlayer()->getLevel()->setBlock(new Vector3($realChest->getX(), $realChest->getY(), $realChest->getZ()), Block::get(Block::AIR));
                $this->isShopping[$event->getPlayer()->getName()] = false;
            }
        }
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $this->lasthit[$player->getName()] = "no";
        $this->isShopping[$player->getName()] = false;
        $player->setNameTag($player->getName());
    }

    public function onRespawn(PlayerRespawnEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();

        if (true/*$this->inArena($player)*/) {
//            $arena = $this->getArena($player);

            $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
            $team = $this->getTeam($player->getNameTag());

            if ($config->getNested("Bed." . $team . ".Alive") == true) {

                $welt = $config->getNested("Spawn." . $team . ".Welt");
                $x = $config->getNested("Spawn." . $team . ".X");
                $y = $config->getNested("Spawn." . $team . ".Y");
                $z = $config->getNested("Spawn." . $team . ".Z");

                $level = $this->getServer()->getLevelByName($welt);

                $event->setRespawnPosition(new Position($x, $y, $z, $level));
            } else {
                $event->setRespawnPosition($this->getServer()->getDefaultLevel()->getSafeSpawn());
                $player->sendMessage($this->prefix . TextFormat::RED . "Votre lit a été détruit, vous ne pouvez plus respawn!");
//                $this->removePlayerFromArena($arena, $name); //todo
                $this->lasthit[$player->getName()] = "no";
                $player->setNameTag($player->getName());
            }

        }
    }

    public function onPickup(InventoryPickupItemEvent $event)
    {
        //todo this lets me think that there's only one currency, the Level... need to rework this
        $player = $event->getInventory()->getHolder();

        if ($player instanceof Player) {
            if (true/*$this->inArena($player)*/) {

                if (!in_array($event->getItem()->getId(), $this->pickup)) {
                    if ($event->getItem()->getItem()->getId() == Item::BRICK) {

                        $event->setCancelled();

                        $player->getLevel()->removeEntity($event->getItem());
                        $this->pickup[] = $event->getItem()->getId();
                        $player->setXpLevel($player->getXpLevel() + 1);
                        $player->sendTip(TextFormat::GOLD . "+" . TextFormat::GREEN . "1 Level!");
                    }

                    if ($event->getItem()->getItem()->getId() == Item::IRON_INGOT) {
                        $event->setCancelled();
                        $player->getLevel()->removeEntity($event->getItem());
                        $this->pickup[] = $event->getItem()->getId();
                        $player->setXpLevel($player->getXpLevel() + 10);
                        $player->sendTip(TextFormat::GOLD . "+" . TextFormat::GREEN . "10 Level!");
                    }

                    if ($event->getItem()->getItem()->getId() == Item::GOLD_INGOT) {
                        $event->setCancelled();
                        $player->getLevel()->removeEntity($event->getItem());
                        $this->pickup[] = $event->getItem()->getId();
                        $player->setXpLevel($player->getXpLevel() + 20);
                        $player->sendTip(TextFormat::GOLD . "+" . TextFormat::GREEN . "20 Level!");
                    }
                }
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if (true/*$this->inArena($player)*/) {
                $event->setDeathMessage("");
//                $arena = $this->getArena($player);
                $cause = $player->getLastDamageCause();
                $players = $this->getPlayers();


                if ($cause instanceof EntityDamageByEntityEvent) {
                    $killer = $cause->getDamager();
                    $event->setDrops(array());
                    if ($killer instanceof Player) {
                        foreach ($players as $pn) {
                            $p = $this->getServer()->getPlayerExact($pn);
                            if ($p != null) {
                                $p->sendMessage($this->prefix . $killer->getNameTag() . TextFormat::GRAY . " a tué " . $player->getNameTag());
                            }
                        }
                    } else {
                        foreach ($players as $pn) {
                            $p = $this->getServer()->getPlayerExact($pn);
                            if ($p != null) {
                                $p->sendMessage($this->prefix . $player->getNameTag() . TextFormat::GRAY . " est mort");
                            }
                        }
                    }
                } else {
                    $event->setDrops(array());
                    foreach ($players as $pn) {
                        $p = $this->getServer()->getPlayerExact($pn);
                        if ($p != null) {

                            if ($this->lasthit[$player->getName()] != "no") {
                                $p2 = $this->getServer()->getPlayerExact($this->lasthit[$player->getName()]);
                                if ($p2 != null) {
                                    $p->sendMessage($this->prefix . $p2->getNameTag() . TextFormat::WHITE . " a tué " . $player->getNameTag());
                                    $this->lasthit[$player->getName()] = "no";
                                } else {
                                    $p->sendMessage($this->prefix . $player->getNameTag() . TextFormat::GRAY . " est mort");
                                }
                            } else {
                                $p->sendMessage($this->prefix . $player->getNameTag() . TextFormat::GRAY . " est mort");
                            }
                        }
                    }
                }
            }
        }
    }

    public function onHit(EntityDamageEvent $event)
    {
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            return;
        } else {
            if (true/*$this->inArena($player)*/) {
                //$arena = $this->getArena($player);

                $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

                if ($config->get("Status") == "Lobby") {
                    $event->setCancelled();
                }
            }
            if ($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();
                if ($damager instanceof Player) {
                    if (true/*$this->inArena($player)*/) {
//                        $arena = $this->getArena($player);

                        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

                        if ($config->get("Status") == "Lobby") {
                            $event->setCancelled();
                        } else {
                            if ($this->getTeam($damager->getNameTag()) == $this->getTeam($player->getNameTag())) {
                                $event->setCancelled();
                                $damager->sendMessage($this->prefix . TextFormat::RED . "Ce joueur est dans votre équipe!");
                            } else {
                                $this->lasthit[$player->getName()] = $damager->getName();
                            }
                        }
                    }
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        if (true/*$this->inArena($player)*/) {
//            $arena = $this->getArena($player);
//            $cause = $player->getLastDamageCause();
//            $players = $this->getPlayers($arena);

            if ($player->getY() <= 4) {
                $player->setHealth(0);
            }

        }
    }


    public function onPlace(BlockPlaceEvent $event)
    {
        if (true) return; //todo fix
        $player = $event->getPlayer();
        $name = $player->getName();
        $block = $event->getBlock();
        if (true/*$this->inArena($player)*/) {

//            $arena = $this->getArena($player);

            $config = new Config($this->getDataFolder() . "Arenas/config.yml", Config::YAML);

            if ($config->get("Status") == "Lobby") {
                $event->setCancelled();

                if ($block->getId() == Block::WOOL) {
                    $item = Item::get($block->getId(), $block->getDamage(), 1);

//                    $arena = $this->getArena($player);
                    $team = $this->getTeamByBlockDamage($block->getDamage());
                    $event->setCancelled();
                    if ($team != $this->getTeam($player->getNameTag())) {
                        if (in_array($team, $this->getAvailableTeams())) {
                            $player->setNameTag($this->getTeamColor($team) . $name);
                            $player->sendMessage($this->prefix . "Vous êtes maintenant en équipe" . TextFormat::GOLD . $team);

                            $player->getInventory()->removeItem($item);
                            $player->getInventory()->addItem($item);
                        } else {
                            $player->sendMessage($this->prefix . "L'équipe " . TextFormat::GOLD . $team . TextFormat::WHITE . " est déjà pleine!");
                            $player->getInventory()->removeItem($item);
                            $player->getInventory()->addItem($item);
                        }
                    } else {
                        $player->sendMessage($this->prefix . "Vous êtes déjà dans l'équipe " . TextFormat::GOLD . $team);
                        $player->getInventory()->removeItem($item);
                        $player->getInventory()->addItem($item);
                    }
                }
            } else {
                if (!in_array($block->getId(), $this->breakableblocks)) {
                    $event->setCancelled();
                }
            }
        }
    }

    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();

        $block = $event->getBlock();
        $block2 = $player->getLevel()->getBlock(new Vector3($block->getX(), $block->getY() - 1, $block->getZ()), false);

        if ($this->inArena($player)) {

            $arena = $this->getArena($player);

            $config = new Config($this->getDataFolder() . "Arenas/config.yml", Config::YAML);

            $team = $this->getTeamByBlockDamage($block2->getDamage());

            if ($config->get("Status") != "Lobby") {

                if ($block->getId() == Block::BED_BLOCK) {

                    if ($team != $this->getTeam($player->getNameTag())) {
                        $config->setNested("Bed." . $team . ".Alive", false);
                        $config->save();
                        $event->setDrops(array());

                        $player->sendMessage($this->prefix . "Vous avez détruit le lit de l'équipe " . $team);

                        foreach ($this->getPlayers($arena) as $pn) {
                            $p = $this->getServer()->getPlayerExact($pn);
                            if ($p != null) {
                                if ($team == $this->getTeam($p->getNameTag())) {
                                    $p->sendMessage($this->prefix . TextFormat::RED . "Le lit de votre équipe a été détruit !");
                                } else {
                                    $p->sendMessage($this->prefix . "Le lit de Team " . TextFormat::GOLD . $team . TextFormat::WHITE . " a été détruit !");
                                }
                            }
                        }
                    } else {
                        $player->sendMessage($this->prefix . "Vous ne pouvez pas détruire votre propre lit");
                        $event->setCancelled();
                    }
                } elseif (!in_array($block->getId(), $this->breakableblocks)) {
                    $event->setCancelled();
                }
            } else {
                $event->setCancelled();
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $block = $event->getBlock();
        $tile = $player->getLevel()->getTile($block);

        if ($this->registerBed == true && $this->registerBedWHO == $name) {
//            $arena = $this->registerBedArena;
            $team = $this->registerBedTeam;
            $this->registerBed = false;
            $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
            $config->setNested("Bed." . $team . ".Welt", $block->getLevel()->getName());
            $config->setNested("Bed." . $team . ".X", $block->getX());
            $config->setNested("Bed." . $team . ".Y", $block->getY());
            $config->setNested("Bed." . $team . ".Z", $block->getZ());
            $config->setNested("Bed." . $team . ".Alive", true);

            $config->save();

            $player->sendMessage(TextFormat::GREEN . "Vous avez enregistré avec succès le lit de Team " . TextFormat::AQUA . $team);
            $player->sendMessage(TextFormat::GREEN . "Setup -> /bw help");
        }

        if ($tile instanceof Sign) {
            $text = $tile->getText();

            if ($this->registerSign == true && $this->registerSignWHO == $name) {

                $arena = $this->registerSignArena;

                $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

                //todo next were moved to member vars
//                $teams = (int)$config->get("Teams");
//                $ppt = (int)$config->get("PlayersPerTeam");
//
//                $maxplayers = $teams * $ppt;


                $tile->setText($this->prefix, $arena . " " . $this->teams . "x" . $this->playerPerTeam, TextFormat::GREEN . "Loading...", TextFormat::YELLOW . "0 / " . $this->teams * $this->playerPerTeam);
                $this->registerSign = false;

                $player->sendMessage(TextFormat::GREEN . "Vous avez enregistré le bouclier ");
                $player->sendMessage(TextFormat::GREEN . "Setup -> /bw help");
            } elseif ($text[0] == $this->prefix) {

                if ($text[2] == TextFormat::GREEN . "entrer") {

                    $arena = substr($text[1], 0, -4);
                    $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                    $status = $config->get("Status");
                    $maxplayers = $config->get("PlayersPerTeam") * $config->get("Teams");
                    $players = count($config->get("Players"));

                    if ($status == "Lobby") {
                        if ($players < $maxplayers) {
                            $this->TeleportToWaitingLobby($arena, $player);
                            $this->setTeamSelectionItems($player, $arena);
                            $this->addPlayerToArena($arena, $name);
                        } else {
                            $player->sendMessage($this->prefix . TextFormat::RED . "Vous ne pouvez pas rejoindre ce match");
                        }
                    } else {
                        $player->sendMessage($this->prefix . TextFormat::RED . "Vous ne pouvez pas rejoindre ce match");
                    }
                } else {
                    $player->sendMessage($this->prefix . TextFormat::RED . "Vous ne pouvez pas rejoindre ce match");
                }

            }
        }

    }

    ###################################    ===[COMMANDS]===     ################################################

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        if (!$sender->isOp()) {
            $sender->sendMessage("you need to be op");
            return false;
        }
        switch ($args[0]) {
            case "state": {
                $sender->sendMessage($this->currentState);
            }
                break;
            case "npc": {
                if ($sender instanceof Player) {
                    $sender->sendMessage("spawn npc");
                    new Merchant($this, $this->shopContent, $sender->getLocation());
                } else
                    $sender->sendMessage("need to be a player to exectue this command");
            }
                break;

        }
        $sender->sendMessage("something");
        return true;
    }

}

abstract class STATE
{
    const IDLE = 0;
    const WAITING = 1;
    const PREPARING = 2;
    const PLAYING = 3;
    const ENDING = 4;
    const CLOSING = 5;
}

###################################    ===[SCHEDULER]===     ###############################################
class OnTick extends PluginTask
{
    public $plugin;
    public $currentTimeWaiting;

    public function __construct(Plugin $owner)
    {
        parent::__construct($owner);
        $this->plugin = $owner;
        if ($this->plugin instanceof Bedwars)
            $this->plugin = $this->plugin;
    }

    public function onRun(int $tick)
    {
        switch ($this->plugin->currentState) {
            case STATE::IDLE: {
                //probably nothing as there's already the onEnable
            }
                break;
            case STATE::WAITING: {
                $this->waiting();
            }
                break;
            case STATE::PREPARING: {
                $this->preparing();
            }
                break;
            case STATE::PLAYING: {
                $this->playing();
            }
                break;
            case STATE::ENDING: {
                $this->ending();
            }
                break;
            case STATE::CLOSING: {
                $this->closing();
            }
        }
    }


    public function waiting()
    {
        if (count(Server::getInstance()->getOnlinePlayers()) < $this->plugin->minPlayer) {
            $this->currentTimeWaiting = $this->plugin->maxTimeWaiting;
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage("Il manque encore " . ($this->plugin->minPlayer - count(Server::getInstance()->getOnlinePlayers())) . " joueurs minimum");
            }
        } else if ($this->currentTimeWaiting > 0) {
            $this->currentTimeWaiting--;
            if ($this->currentTimeWaiting % 20 == 0) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->sendMessage("La partie commence dans " . $this->currentTimeWaiting / 20 . " secondes");
                }
            }
        } else {
            //start game !
            $this->plugin->currentState = "PREPARING";
        }

    }

    public function preparing()
    {
        //todo ... do something, like prepare beds, prepare shops, clean, ...
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $player->getInventory()->clearAll();
            $player->sendMessage("Go !");
        }
        $this->plugin->currentState = "PLAYING";
    }

    public function playing()
    {
        if (count($this->plugin->getAliveTeams()) < 2) {
            $this->plugin->debug("END GAME !");
            $this->plugin->debug($this->plugin->getAliveTeams());
        }
    }

    public function ending()
    {

    }

    public function closing()
    {

    }


}
