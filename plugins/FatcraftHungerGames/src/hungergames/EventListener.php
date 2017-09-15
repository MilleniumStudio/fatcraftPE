<?php

namespace hungergames;

use fatutils\game\GameManager;
use fatutils\players\PlayersManager;
use fatutils\tools\Sidebar;
use fatutils\tools\WorldUtils;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
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
                score\HungerGameScoreManager::getInstance()->registerDeath($p->getPlayer());
		PlayersManager::getInstance()->getFatPlayer($p)->setHasLost(true);

        WorldUtils::addStrike($p->getLocation());
            $l_PlayerLeft = PlayersManager::getInstance()->getAlivePlayerLeft();


            foreach (HungerGame::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            {
            $l_Player->sendMessage($e->getDeathMessage());
                if ($l_PlayerLeft > 1)
                    $l_Player->sendMessage("Il reste " . TextFormat::YELLOW . PlayersManager::getInstance()->getAlivePlayerLeft() . TextFormat::RESET . " survivants !", "*");
            }

            if ($l_PlayerLeft <= 1 && !GameManager::getInstance()->isGameFinished())
                HungerGame::getInstance()->endGame();

        $e->setDeathMessage("");
		$p->setGamemode(3);

            Sidebar::getInstance()->update();
	}

	/**
	 * @param BlockBreakEvent $e
	 */
	public function onBlockBreak(BlockBreakEvent $e)
	{
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
		$p = $e->getPlayer();
		$p->setGamemode(2);
		$p->getInventory()->clearAll();

		HungerGame::getInstance()->handlePlayerConnection($p);
	}
}
