<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 13:51
 */

namespace fatcraft\lobby;

use fatutils\FatUtils;
use fatutils\tools\WorldUtils;
use fatutils\tools\DelayedExec;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use fatcraft\lobby\commands\MenuCommand;
use fatutils\holograms\HologramsManager;

class Lobby extends PluginBase implements Listener
{
    const CONFIG_KEY_WELCOME_TITLE = "welcomeTitle";
    const CONFIG_KEY_WELCOME_SUBTITLE = "welcomeSubtitle";

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
    }

    public function onPlayerJoin(PlayerJoinEvent $e)
    {
        new DelayedExec(5, function () use ($e) {
            $e->getPlayer()->addTitle($this->getConfig()->get(Lobby::CONFIG_KEY_WELCOME_TITLE, ""));
            $e->getPlayer()->addSubTitle($this->getConfig()->get(Lobby::CONFIG_KEY_WELCOME_SUBTITLE, ""));
        });
        // Items in player bar
//        $e->getPlayer()->getInventory()->setHeldItemIndex(4);
//        $l_Item1 = \pocketmine\item\Item::get(\pocketmine\item\Item::COMPASS);
//        $e->getPlayer()->getInventory()->setItem(2, $l_Item1);
//        $l_Item2 = \pocketmine\item\Item::get(\pocketmine\item\Item::NETHERSTAR);
//        $e->getPlayer()->getInventory()->setItem(6, $l_Item2);
//        $e->getPlayer()->getInventory()->sendContents($e->getPlayer());
    }

    // actions on item select / touch
    public function onPlayerItemHeld(PlayerItemHeldEvent $p_Event)
    {
//        if ($p_Event->getItem()->getId() == \pocketmine\item\Item::COMPASS)
//        {
//            WindowsManager::getInstance()->sendMenu($p_Event->getPlayer(), WindowsManager::WINDOW_BUTTON_MENU);
//            $p_Event->getPlayer()->getInventory()->setHeldItemIndex(4);
//        }
//        elseif ($p_Event->getItem()->getId() == \pocketmine\item\Item::NETHERSTAR)
//        {
//            WindowsManager::getInstance()->sendMenu($p_Event->getPlayer(), WindowsManager::WINDOW_INPUT_MENU);
//            $p_Event->getPlayer()->getInventory()->setHeldItemIndex(4);
//        }
    }

    // disable all inventory items move
    public function onInventoryTransaction(InventoryTransactionEvent $p_Event)
    {
        $p_Event->setCancelled(TRUE);
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