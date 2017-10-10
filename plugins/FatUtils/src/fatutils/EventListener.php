<?php

namespace fatutils;

use fatutils\players\PlayersManager;
use fatutils\tools\DelayedExec;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\event\player\PlayerDeathEvent;
use fatutils\gamedata\GameDataManager;

class EventListener implements Listener
{
    /**
     * @param PlayerJoinEvent $e
     * @priority LOWEST
     */
    public function onJoin(PlayerJoinEvent $e)
    {
        $p = $e->getPlayer();
        $p->getInventory()->clearAll();

        if (!PlayersManager::getInstance()->fatPlayerExist($p))
            PlayersManager::getInstance()->addPlayer($p);
        else
        {
            FatUtils::getInstance()->getLogger("Reapplying player to FatPlayer");
            PlayersManager::getInstance()->getFatPlayer($p)->setPlayer($p);
        }

        new DelayedExec(1, function () use ($p)
        {
            PlayersManager::getInstance()->getFatPlayer($p)->updateFormattedNameTag();
        });
    }

    public function onQuit(PlayerQuitEvent $e)
    {
        $p = $e->getPlayer();
        PlayersManager::getInstance()->removePlayer($p);
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerDamage(EntityDamageEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
        {
            if (PlayersManager::getInstance()->getFatPlayer($p)->isHealthDisplayed())
            {
                new DelayedExec(1, function () use ($p)
                {
                    PlayersManager::getInstance()->getFatPlayer($p)->updateFormattedNameTag();
                });
            }
        }
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerRegen(EntityRegainHealthEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
        {
            if (PlayersManager::getInstance()->getFatPlayer($p)->isHealthDisplayed())
            {
                new DelayedExec(1, function () use ($p)
                {
                    PlayersManager::getInstance()->getFatPlayer($p)->updateFormattedNameTag();
                });
            }
        }
    }

    public function playerDeathEvent(PlayerDeathEvent $p_Event)
    {
        $l_Player = $p_Event->getEntity();
        $l_Killer = null;
        if ($l_Player->getLastDamageCause()->getEntity() instanceof Player)
        {
            $l_Killer = $l_Player->getLastDamageCause()->getEntity();
            GameDataManager::getInstance()->recordKill($l_Killer->getUniqueId(), $l_Player->getName());
        }
        else
        {
            // see pocketmine\event\entity\EntityDamageEvent for details
            $l_Killer = $l_Player->getLastDamageCause()->getCause();
        }
        GameDataManager::getInstance()->recordDeath($l_Player->getUniqueId(), $l_Killer);
    }
}
