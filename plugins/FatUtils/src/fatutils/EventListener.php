<?php

namespace fatutils;

use fatutils\ban\BanManager;
use fatutils\game\GameManager;
use fatutils\players\PlayersManager;
use fatutils\tools\DelayedExec;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\event\player\PlayerDeathEvent;
use fatutils\gamedata\GameDataManager;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{
    public function onLogin(PlayerLoginEvent $e)
    {
        if (BanManager::getInstance()->isBanned($e->getPlayer()))
        {
            $l_ExpirationTimestamp = BanManager::getInstance()->getPlayerBan($e->getPlayer())->getExpirationTimestamp();
            if (!is_null($l_ExpirationTimestamp))
                $e->setKickMessage("You're banned from this server until " . date("D M j G:i:s Y", $l_ExpirationTimestamp) . ".");
            else
                $e->setKickMessage("You're definitely banned from this server.");
            $e->setCancelled(true);
        }

        FatUtils::getInstance()->getLogger()->info("[LOGIN EVENT] from " . $e->getPlayer()->getName() . "(" . $e->getPlayer()->getUniqueId()->toString() . ") => " . ($e->isCancelled() ? "CANCELLED" : "ACCEPTED"));
    }

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
            FatUtils::getInstance()->getLogger()->info("Reapplying player to FatPlayer");
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
        if (GameManager::getInstance()->isWaiting())
            PlayersManager::getInstance()->removePlayer($p);
    }

	public function onPlayerChat(PlayerChatEvent $e)
	{
		if (PlayersManager::getInstance()->fatPlayerExist($e->getPlayer()))
		{
			$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($e->getPlayer());
			if ($l_FatPlayer->isMuted())
			{
				$e->getPlayer()->sendMessage(TextFormat::RED . "You've been muted until " . date("Y-m-d H:i:s", $l_FatPlayer->getMutedExpiration()));
				$e->setCancelled(true);
			}
		}
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
        $l_Killer = (!is_null($l_Player->getLastDamageCause()) ? $l_Player->getLastDamageCause()->getEntity() : null);
        if ($l_Killer instanceof Player)
            GameDataManager::getInstance()->recordKill($l_Killer->getUniqueId(), $l_Player->getName());
        else
            $l_Killer = $l_Player->getLastDamageCause()->getCause(); // see pocketmine\event\entity\EntityDamageEvent for details

        GameDataManager::getInstance()->recordDeath($l_Player->getUniqueId(), $l_Killer);
    }
}
