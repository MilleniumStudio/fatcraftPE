<?php

namespace hungergames;

use fatutils\players\PlayersManager;
use fatutils\tools\WorldUtils;
use plugins\FatUtils\src\fatutils\game\GameManager;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Location;
use function Sodium\library_version_major;

class EventListener implements Listener
{

	public function __construct()
	{
	}

	/**
	 * @param PlayerMoveEvent $e
	 */
	public function onMove(PlayerMoveEvent $e)
	{

	}

	/**
	 * @param SignChangeEvent $e
	 */
	public function onSignChange(SignChangeEvent $e)
	{
		$p = $e->getPlayer();
	}

	/**
	 * @param PlayerInteractEvent $e
	 */
	public function onInteract(PlayerInteractEvent $e)
	{

	}

	/**
	 * @param PlayerQuitEvent $e
	 */
	public function playerQuitEvent(PlayerQuitEvent $e)
	{
		$p = $e->getPlayer();
	}

	/**
	 * @param PlayerDeathEvent $e
	 */
	public function playerDeathEvent(PlayerDeathEvent $e)
	{
		$p = $e->getEntity();
	}

	/**
	 * @param BlockBreakEvent $e
	 */
	public function onBlockBreak(BlockBreakEvent $e)
	{

	}

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
