<?php

namespace fatutils\npcs\functions;

use pocketmine\Player;

abstract class NPCFunction
{
    public $name;
    public $npc;

    public function __construct($name, &$npc)
    {
        $this->name = $name;
        $this->npc = $npc;
    }

    abstract public function onTick(int $currentTick);

    abstract public function onInterract(Player $player);
}