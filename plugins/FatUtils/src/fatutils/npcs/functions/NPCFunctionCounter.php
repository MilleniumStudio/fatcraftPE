<?php

namespace fatutils\npcs\functions;

use pocketmine\Player;

class NPCFunctionCounter extends NPCFunction
{

    private $m_OriginalName = "";
    private $m_Counter = 0;

    public function __construct(&$npc)
    {
        parent::__construct("NPCFunctionCounter", $npc);
        $this->m_OriginalName = $npc->getNameTag();
    }

    public function onTick(int $currentTick)
    {
    }

    public function onInterract(Player $player)
    {
        $this->m_Counter++;
        $this->npc->setNameTag($this->m_OriginalName . "\n" . strval($this->m_Counter));
    }
}

