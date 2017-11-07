<?php

namespace fatutils\npcs\functions;

use pocketmine\Player;
use fatutils\tools\WorldUtils;

class NPCFunctionTeleport extends SignFunction
{
    private $m_Destination = null;

    public function __construct(&$sign)
    {
        parent::__construct("SignFunctionTeleport", $sign);
        var_dump($sign->data);
        if (isset($sign->data["to"]))
        {
            $this->m_Destination = WorldUtils::stringToLocation($sign->data["to"]);
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
