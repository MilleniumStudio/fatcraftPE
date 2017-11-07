<?php

namespace fatutils\npcs\functions;

use pocketmine\Player;
use fatutils\tools\WorldUtils;

class NPCFunctionTeleport extends NPCFunction
{
    private $m_Destination = null;

    public function __construct(&$npc)
    {
        parent::__construct("NPCFunctionTeleport", $npc);
        if (isset($npc->data["to"]))
        {
            $this->m_Destination = WorldUtils::stringToLocation($npc->data["to"]);
        }
    }

    public function onTick(int $currentTick)
    {
    }

    public function onInterract(Player $player)
    {
        if ($this->m_Destination !== null)
        {
            $player->teleport($this->m_Destination);
        }
    }

}
