<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 13:51
 */

namespace fatcraft\lobby;

use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\tools\TextFormatter;
use fatutils\tools\WorldUtils;
use fatutils\tools\DelayedExec;
use fatutils\ui\impl\GamesWindow;
use fatutils\ui\impl\LobbiesWindow;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use fatcraft\lobby\commands\MenuCommand;
use fatutils\holograms\HologramsManager;

class Lobby extends PluginBase implements Listener
{
    private static $m_Instance;

    public static function getInstance(): Lobby
    {
        return self::$m_Instance;
    }

    public function onLoad()
    {
        self::$m_Instance = $this;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        WorldUtils::stopWorldsTime();
//        $this->getCommand("menu")->setExecutor(new MenuCommand($this));
        HologramsManager::getInstance();
        FatPlayer::$m_OptionDisplayHealth = false;
    }

    public function onPlayerJoin(PlayerJoinEvent $e)
    {
        new DelayedExec(5, function () use ($e) {
            $e->getPlayer()->addTitle(
            	(new TextFormatter("lobby.welcome.title"))->asStringForPlayer($e->getPlayer()),
				(new TextFormatter("lobby.welcome.subtitle", ["name" => $e->getPlayer()->getName()]))->asStringForPlayer($e->getPlayer())
			);
        });

        // Items in player bar
        $e->getPlayer()->getInventory()->setHeldItemIndex(4);
        $l_Item1 = Item::get(ItemIds::COMPASS);
        $e->getPlayer()->getInventory()->setItem(2, $l_Item1);
        $l_Item2 = Item::get(ItemIds::NETHERSTAR);
        $e->getPlayer()->getInventory()->setItem(6, $l_Item2);
        $e->getPlayer()->getInventory()->sendContents($e->getPlayer());
    }

    // actions on item select / touch
    public function onPlayerUseItem(PlayerInteractEvent $p_Event)
    {
        if ($p_Event->getItem()->getId() == ItemIds::COMPASS)
            new GamesWindow($p_Event->getPlayer());
        elseif ($p_Event->getItem()->getId() == ItemIds::NETHERSTAR)
			new LobbiesWindow($p_Event->getPlayer());
    }

    // disable all inventory items move
    public function onInventoryTransaction(InventoryTransactionEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onItemPickup(InventoryPickupItemEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onArrowPickup(InventoryPickupArrowEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onPlayerDropItem(PlayerDropItemEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onPlayerExhaust(PlayerExhaustEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onPlayerDamage(EntityDamageEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
            $e->setCancelled(true);
    }
}