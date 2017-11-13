<?php

namespace fatutils\npcs\functions;

use pocketmine\Player;
use fatutils\shop\ShopManager;

class NPCFunctionShop extends NPCFunction
{
    private $m_Destination = null;

    public function __construct(&$npc)
    {
        parent::__construct("NPCFunctionShop", $npc);
    }

    public function onTick(int $currentTick)
    {
    }

    public function onInterract(Player $player)
    {
        ShopManager::getInstance()->getShopMenu($player)->open();
    }

}
