<?php

namespace hungergames;

use fatutils\game\GameManager;
use fatutils\players\PlayersManager;
use fatutils\scores\ScoresManager;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\tools\Sidebar;
use fatutils\tools\WorldUtils;
use fatutils\spawns\SpawnManager;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{

    public function __construct()
    {
    }

    /**
     * @param PlayerDeathEvent $e
     */
    public function playerDeathEvent(PlayerDeathEvent $e)
    {
        $p = $e->getEntity();
        if (!GameManager::getInstance()->isWaiting())
        {
            PlayersManager::getInstance()->getFatPlayer($p)->setOutOfGame(true);

            WorldUtils::addStrike($p->getLocation());
            $l_PlayerLeft = PlayersManager::getInstance()->getInGamePlayerLeft();

            ScoresManager::getInstance()->giveRewardToPlayer($p->getUniqueId(), ((GameManager::getInstance()->getPlayerNbrAtStart() - $l_PlayerLeft) / GameManager::getInstance()->getPlayerNbrAtStart()));

            foreach (HungerGame::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            {
                $l_Player->sendMessage($e->getDeathMessage());
                if ($l_PlayerLeft > 1)
                    $l_Player->sendMessage("Il reste " . TextFormat::YELLOW . PlayersManager::getInstance()->getInGamePlayerLeft() . TextFormat::RESET . " survivants !");
            }

            if ($l_PlayerLeft <= 1 && !GameManager::getInstance()->isGameFinished())
                HungerGame::getInstance()->endGame();

            $e->setDeathMessage("");
            $p->setGamemode(3);

            Sidebar::getInstance()->update();
        }
    }

    /**
     * @param BlockBreakEvent $e
     */
    public function onBlockBreak(BlockBreakEvent $e)
    {
        if (!HungerGame::getInstance()->getHungerGameConfig()->isSkyWars())
            $e->setCancelled(true);
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
        $p_Player = $e->getPlayer();
        if (GameManager::getInstance()->isPlaying()) {
            if ($p_Player->isOp()) {
                $p_Player->setGamemode(3);
                PlayersManager::getInstance()->getFatPlayer($p_Player)->setOutOfGame();
                return;
            }
            else
            {
                LoadBalancer::getInstance()->balancePlayer($p_Player, LoadBalancer::TEMPLATE_TYPE_LOBBY);
                $e->setCancelled();
                return;
            }
        }

        $p = $e->getPlayer();
        $p->setGamemode(2);
        $p->getInventory()->clearAll();

        HungerGame::getInstance()->handlePlayerConnection($p);
    }

    public function onPlayerRespawn(PlayerRespawnEvent $p_Event)
    {
        if (GameManager::getInstance()->isWaiting())
        {
            $spawn = SpawnManager::getInstance()->getRandomEmptySpawn();
            $position = \pocketmine\level\Position::fromObject($spawn->getLocation()->add(-0.5, 0.1, -0.5), $spawn->getLocation()->getLevel());
            $p_Event->setRespawnPosition($position);
            HungerGame::getInstance()->getLogger()->info("Player " . $p_Event->getPlayer()->getName() . " respawn at " . $position->__toString());
        }
        else{
            new DelayedExec(function () use ($p_Event)
            {
                $p_Event->getPlayer()->setGamemode(3);
                $p_Event->getPlayer()->teleport(HungerGame::getInstance()->getHungerGameConfig()->getDeathArenaLoc());
            }, 5);
        }
    }

    public function onChunkUnload(\pocketmine\event\level\ChunkUnloadEvent $p_event)
    {
        $p_event->setCancelled();
    }
}
