<?php

namespace fatutils;

use fatutils\players\PlayersManager;
use fatutils\tools\Timer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener
{
	/**
	 * @param PlayerJoinEvent $e
	 */
	public function onSpawn(PlayerJoinEvent $e)
	{
		$p = $e->getPlayer();
		$p->getInventory()->clearAll();
		PlayersManager::getInstance()->addPlayer($p);
	}

	public function onQuit(PlayerQuitEvent $e)
	{
		$p = $e->getPlayer();
		PlayersManager::getInstance()->removePlayer($p);
	}
}
