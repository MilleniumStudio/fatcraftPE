<?php

namespace fatutils\signs;

use pocketmine\Player;

class MultipleSigns extends CustomSign
{
    public $signs;

    public function __construct($name, &$tiles)
    {
        parent::__construct($name);
        $this->signs = $tiles;
    }

    public function onTick(int $currentTick)
    {
        if ($this->update)
        {
            foreach ($this->signs as $sign)
            {
                if ($sign->function !== null)
                {
                    $sign->function->onTick($currentTick);
                }
            }
        }
    }

    public function updateTexte()
    {
        foreach ($this->signs as $sign)
        {
            if ($sign->sign instanceof Sign)
            {
                $sign->pdateTexte();
            }
        }
    }

    public function onInterract(Player $player, int $p_Index = -1)
    {
        if ($this->function !== null)
        {
            $this->function->onInterract($player, $p_Index);
        }
    }
}