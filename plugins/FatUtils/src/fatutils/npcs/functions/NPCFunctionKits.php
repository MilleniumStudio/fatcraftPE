<?php

namespace fatutils\npcs\functions;

use fatutils\players\Kit;
use fatutils\tools\TextFormatter;
use pocketmine\Player;
use fatutils\players\PlayersManager;
use fatutils\shop\ShopItem;
use fatutils\tools\ItemUtils;
use pocketmine\utils\TextFormat;

class NPCFunctionKits extends NPCFunction
{
	private $headItem;
	private $chestItem;
	private $pantsItem;
	private $bootsItem;
	private $heldItem;

	private $equipment;
	private $phraseTemplate;

	public function __construct(&$npc)
    {
        parent::__construct("NPCFunctionKits", $npc);
		if (isset($npc->equipment))
			$this->equipment = $npc->equipment;
    }

    public function onTick(int $currentTick)
    {
    }

    public function onInterract(Player $p_Player)
	{
		$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

		$l_FatPlayer->clearKitItems();

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

		// there is no NPC entity so and i don't feel like add phrase an element like "phraseTemplate" to Entity
		// so TODO : create a NPC entity or something
		if (substr($this->equipment["chest"], 14, -2) == "LEATHER_CHESTPLATE")
		{
			if (substr($this->equipment["held"], 14, -2) == "IRON_SWORD")
				$p_Player->sendMessage((new TextFormatter("kits.equiped.swordman"))->asStringForPlayer($p_Player));
			else
				$p_Player->sendMessage((new TextFormatter("kits.equiped.archer"))->asStringForPlayer($p_Player));
		}
		else if (substr($this->equipment["chest"], 14, -2) == "CHAIN_CHESTPLATE")
			$p_Player->sendMessage((new TextFormatter("kits.equiped.tank"))->asStringForPlayer($p_Player));
		else if (substr($this->equipment["chest"], 14, -2) == "ELYTRA")
			$p_Player->sendMessage((new TextFormatter("kits.equiped.elytra"))->asStringForPlayer($p_Player));
    }
}
