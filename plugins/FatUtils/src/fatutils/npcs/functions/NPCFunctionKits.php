<?php

namespace fatutils\npcs\functions;

use fatutils\players\Kit;
use pocketmine\Player;
use fatutils\players\PlayersManager;
use fatutils\shop\ShopItem;
use fatutils\tools\ItemUtils;

class NPCFunctionKits extends NPCFunction
{
	private $headItem;
	private $chestItem;
	private $pantsItem;
	private $bootsItem;
	private $heldItem;

	private $equipment;

	public function __construct(&$npc)
    {
        parent::__construct("NPCFunctionKits", $npc);
		if (isset($npc->equipment))
            $this->equipment = $npc->equipment;
    }

    public function onTick(int $currentTick)
    {
    }

    public function onInterract(Player $player)
    {
		$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($player);

		$l_FatPlayer->emptySlot("headItem");
		$l_FatPlayer->emptySlot("chestItem");
		$l_FatPlayer->emptySlot("pantsItem");
		$l_FatPlayer->emptySlot("bootsItem");
		$l_FatPlayer->emptySlot("heldItem");

		if (isset($this->equipment["head"]))
			$l_FatPlayer->setKitItem(Kit::SLOT_KIT_HEAD, ItemUtils::getItemFromRaw($this->equipment["head"]));
		if (isset($this->equipment["chest"]))
			$l_FatPlayer->setKitItem(Kit::SLOT_KIT_CHEST, ItemUtils::getItemFromRaw($this->equipment["chest"]));
		if (isset($this->equipment["pants"]))
			$l_FatPlayer->setKitItem(Kit::SLOT_KIT_PANTS, ItemUtils::getItemFromRaw($this->equipment["pants"]));
		if (isset($this->equipment["boots"]))
			$l_FatPlayer->setKitItem(Kit::SLOT_KIT_BOOTS, ItemUtils::getItemFromRaw($this->equipment["boots"]));
		if (isset($this->equipment["held"]))
			$l_FatPlayer->setKitItem(Kit::SLOT_KIT_HELD, ItemUtils::getItemFromRaw($this->equipment["held"]));
		$l_FatPlayer->syncKitItems();
    }
}
