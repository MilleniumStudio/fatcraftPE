<?php

namespace fatutils\signs\functions;

use pocketmine\Player;

abstract class SignFunction
{
    public $name;
    public $sign;

    public function __construct($name, &$sign)
    {
        $this->name = $name;
        $this->sign = $sign;
    }

    abstract public function onTick(int $currentTick);

    abstract public function onInterract(Player $player, int $p_Index = -1);
}

