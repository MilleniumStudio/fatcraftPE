<?php

namespace fatcraft\bedwars;

use fatcraft\bedwars\Bedwars;
use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\game\GameManager;
use fatutils\players\PlayersManager;
use fatutils\teams\TeamsManager;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
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
    public function onPlayerPickup(InventoryPickupItemEvent $e)
    {
        $l_Viewers = $e->getInventory()->getViewers();
        if (count($l_Viewers) > 0) {
            $p = array_values($l_Viewers)[0];
            if ($p instanceof Player) {
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
        if ($e->getBlock()->getId() == Bedwars::getInstance()->getBedBlockId())
        {
            $l_PlayerTeam = TeamsManager::getInstance()->getPlayerTeam($e->getPlayer());

            if (isset($l_PlayerTeam) && WorldUtils::getDistanceBetween($e->getBlock(), $l_PlayerTeam->getSpawn()->getLocation()) < 2) {
                if (Bedwars::DEBUG) {
                    echo "break your own bed authorized cause debug is on\n";
                } else {
                    if (!Bedwars::getInstance()->getBedwarsConfig()->isFastRush())
                        $e->getPlayer()->sendMessage((new TextFormatter("bedwars.bed.destroyed.cancelled"))->asStringForPlayer($e->getPlayer()));
                    else
                        $e->getPlayer()->sendMessage((new TextFormatter("fastRush.bed.destroyed.cancelled"))->asStringForPlayer($e->getPlayer()));
                    $e->setCancelled(true);
                }
            } else {
                FatUtils::getInstance()->getLogger()->info("Bed destroyed !");

                new DelayedExec(function ()
				{
                    if (!Bedwars::getInstance()->getBedwarsConfig()->isFastRush())
                        PlayersManager::getInstance()->sendMessageToOnline(new TextFormatter("bedwars.bed.destroyed"));
                    else
                        PlayersManager::getInstance()->sendMessageToOnline(new TextFormatter("fastRush.bed.destroyed"));

                    Sidebar::getInstance()->update();
				}, 1);
                $e->setDrops([]);
            }
        } else {
            if (!Bedwars::DEBUG && !$e->getBlock()->hasMetadata("isCustom"))
            {
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

    /**
     * @param PlayerJoinEvent $e
     */
    public function onSpawn(PlayerJoinEvent $e)
    {
        $p_Player = $e->getPlayer();

        if (GameManager::getInstance()->isPlaying() || count(LoadBalancer::getInstance()->getServer()->getOnlinePlayers()) > PlayersManager::getInstance()->getMaxPlayer())
        {
            if ($p_Player->isOp())
            {
                $p_Player->setGamemode(3);
                PlayersManager::getInstance()->getFatPlayer($p_Player)->setOutOfGame();
                return;
            }
            else
            {
                LoadBalancer::getInstance()->balancePlayer($p_Player, LoadBalancer::TEMPLATE_TYPE_LOBBY);
                return;
            }
        }


        $p = $e->getPlayer();
        $p->getInventory()->clearAll();

        Bedwars::getInstance()->handlePlayerConnection($p);
    }

    public function onChunkUnload(\pocketmine\event\level\ChunkUnloadEvent $p_event)
    {
        $p_event->setCancelled();
    }
}
