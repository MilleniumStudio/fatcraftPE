<?php

namespace fatutils\signs\functions;

use pocketmine\Player;
use fatutils\tools\WorldUtils;

class SignFunctionTeleport extends SignFunction
{
    private $m_Destination = null;

    public function __construct(&$sign)
    {
        parent::__construct("SignFunctionTeleport", $sign);
        if (isset($sign->data["to"]))
        {
            $this->m_Destination = WorldUtils::stringToLocation($sign->data["to"]);
        }
        else
        {
            throw Exception("SignFunctionTeleport has no destination !");
        }
    }

    public function onTick(int $currentTick): bool
    {
        return true;
    }

    public function onInterract(Player $player, int $p_Index = -1)
    {
        if ($this->m_Destination !== null)
        {
            $player->teleport($this->m_Destination);
        }
    }

}
