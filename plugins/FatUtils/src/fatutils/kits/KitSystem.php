<?php
/**
 * Created by PhpStorm.
 * User: Naphtaline
 * Date: 28/11/2017
 * Time: 17:35
 */

namespace fatutils\npcs;

use fatutils\FatUtils;
use pocketmine\event\Listener;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use pocketmine\Player;

class enumKits implements Listener
{
	const kit0 = "kit1";

	const weapon = "weapon";
	const chest = "chest";
	const lega = "legs";
	const feet = "feet";
	const helmet = "helmet";
}

class KitSystem extends PluginBase
{
	private $kits;

	public function __construct()
	{
		$this->kits[enumKits::kit0] = array(enumKits::weapon => ItemIds::IRON_SHOVEL, enumKits::helmet => ItemIds::IRON_HELMET);
	}

	public function equipPlayer(Player $player, string $kitName)
	{
		if ($player == null)
		{
			$this->getLogger()->error("KitSystem::equipPlayer : player == null");
			return;
		}
		if (!isset($this->kits[$kitName]))
		{
			$this->getLogger()->error("KitSystem::equipPlayer : kit <" . $kitName . "> does not exist.");
			return;
		}

		if (isset($this->kits[$kitName][enumKits::helmet]))
			$player->getInventory()->setHelmet($this->kits[$kitName][enumKits::helmet]);
		if (isset($this->kits[$kitName][enumKits::chest]))
			$player->getInventory()->setChestplate($this->kits[$kitName][enumKits::chest]);
		if (isset($this->kits[$kitName][enumKits::legs]))
			$player->getInventory()->setChestplate($this->kits[$kitName][enumKits::legs]);
		if (isset($this->kits[$kitName][enumKits::feet]))
			$player->getInventory()->setHelmet($this->kits[$kitName][enumKits::feet]);
	}
}
