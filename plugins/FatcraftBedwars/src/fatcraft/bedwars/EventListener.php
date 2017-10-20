<?php

namespace fatcraft\bedwars;

use fatcraft\bedwars\Bedwars;
use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\teams\TeamsManager;
use fatutils\tools\DelayedExec;
use fatutils\tools\Sidebar;
use fatutils\tools\WorldUtils;
use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\ItemIds;
use pocketmine\metadata\MetadataValue;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{
//	/**
//	 * @param PlayerDeathEvent $e
//	 */
//	public function playerDeathEvent(PlayerDeathEvent $e)
//	{
//		$p = $e->getEntity();
//


//		PlayersManager::getInstance()->getFatPlayer($p)->setHasLost(true);
//
//        WorldUtils::addStrike($p->getLocation());
//        $l_PlayerLeft = PlayersManager::getInstance()->getAlivePlayerLeft();
//
//        foreach (Bedwars::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
//        {
//            $l_Player->sendMessage($e->getDeathMessage());
//            if ($l_PlayerLeft > 1)
//                $l_Player->sendMessage("Il reste " . TextFormat::YELLOW . PlayersManager::getInstance()->getAlivePlayerLeft() . TextFormat::RESET . " survivants !", "*");
//        }
//
//        if ($l_PlayerLeft <= 1 && !Bedwars::DEBUG)
//            Bedwars::getInstance()->endGame();
//
//        $e->setDeathMessage("");
//		$p->setGamemode(3);
//
//        Sidebar::getInstance()->update();
//	}

    public function onPlayerPickup(InventoryPickupItemEvent $e)
    {
        $l_Viewers = $e->getInventory()->getViewers();
//        var_dump($l_Viewers);
        if (count($l_Viewers) > 0) {
            $p = array_values($l_Viewers)[0];
//            FatUtils::getInstance()->getLogger()->info(gettype($p));
            if ($p instanceof Player) {
//                FatUtils::getInstance()->getLogger()->info("InventoryPickupItemEvent " . $e->getItem() . " from " . $p->getName() . "> " . $e->getItem()->getItem()->getId());
                switch ($e->getItem()->getItem()->getId()) {
                    case ItemIds::IRON_INGOT:
                        Bedwars::getInstance()->modPlayerIron($p, $e->getItem()->getItem()->getCount());

                        $e->setCancelled(true);
                        $e->getItem()->kill();
                        break;
                    case ItemIds::GOLD_INGOT:
                        Bedwars::getInstance()->modPlayerGold($p, $e->getItem()->getItem()->getCount());

                        $e->setCancelled(true);
                        $e->getItem()->kill();
                        break;
                    case ItemIds::DIAMOND:
                        Bedwars::getInstance()->modPlayerDiamond($p, $e->getItem()->getItem()->getCount());

                        $e->setCancelled(true);
                        $e->getItem()->kill();
                        break;
                }

                Sidebar::getInstance()->updatePlayer($p);
            }
        }
    }

    /**
     * @param BlockBreakEvent $e
     */
    public function onBlockBreak(BlockBreakEvent $e)
    {
//	    FatUtils::getInstance()->getLogger()->info("BlockBreakEvent ==>");
        if ($e->getBlock()->getId() == Bedwars::BLOCK_ID) {
            $l_PlayerTeam = TeamsManager::getInstance()->getPlayerTeam($e->getPlayer());

            if (isset($l_PlayerTeam) && WorldUtils::getDistanceBetween($e->getBlock(), $l_PlayerTeam->getSpawn()->getLocation()) < 2) {
                if (Bedwars::DEBUG) {
                    echo "bed your own bed authorized cause debug is on\n";
                } else {
                    FatUtils::getInstance()->getLogger()->info("destroy of bed cancelled");
                    $e->setCancelled(true);
                }
            } else {
                FatUtils::getInstance()->getLogger()->info("Bed destroyed !");

                new DelayedExec(function ()
				{
					Sidebar::getInstance()->update();
				}, 1);
            }
        } else {
            if (Bedwars::DEBUG)
                echo "authorized break cause debug is on !\n";
            else if (!$e->getBlock()->hasMetadata("isCustom"))
            {
//                echo "block break cancelled cause block is not custom\n";
                $e->setCancelled(true);
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $e)
    {
        new DelayedExec(function ()
		{
			Sidebar::getInstance()->update(); //todo remove (debug)
		}, 1);

        $e->getBlock()->setMetadata("isCustom", new class(Bedwars::getInstance()) extends MetadataValue
        {
            /**
             *  constructor.
             */
            public function __construct(PluginBase $p_Plugin)
            {
                parent::__construct($p_Plugin);
            }


            /**
             * Fetches the value of this metadata item.
             *
             * @return mixed
             */
            public function value()
            {
                return true;
            }

            /**
             * Invalidates this metadata item, forcing it to recompute when next
             * accessed.
             */
            public function invalidate()
            {
            }
        });
//        if ($e->getBlock()->hasMetadata("isCustom"))
//            echo "Place block with custom meta\n";
    }

//    /**
//     * @param EntityDamageEvent $e
//     */
//    public function onEntityDamageEvent(EntityDamageEvent $e)
//    {
//        if (GameManager::getInstance()->getSecondSinceStart() < 30)
//            $e->setCancelled(true);
//    }


    /**
     * @param PlayerJoinEvent $e
     */
    public function onSpawn(PlayerJoinEvent $e)
    {
        $p = $e->getPlayer();
        $p->getInventory()->clearAll();

        Bedwars::getInstance()->handlePlayerConnection($p);
    }
}
