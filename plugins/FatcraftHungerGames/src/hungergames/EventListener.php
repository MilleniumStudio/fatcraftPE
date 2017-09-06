<?php

namespace hungergames;

use fatutils\players\PlayersManager;
use fatutils\tools\WorldUtils;
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
		echo "HG interact";
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

		foreach (HungerGame::getInstance()->getHungerGameConfig()->getSlots() as $l_Slot)
		{
			if ($l_Slot instanceof Location)
			{
				$l_NearbyEntities = $l_Slot->getLevel()
					->getNearbyEntities(WorldUtils::getRadiusBB($l_Slot, doubleval(1)));

				if (count($l_NearbyEntities) == 0)
				{
					echo $l_Slot . " available !\n";
					$p->teleport($l_Slot);
					break;
				}
			}
		}
	}
}
