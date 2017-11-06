<?php

namespace fatutils\signs\functions;

use pocketmine\Player;

class SignFunctionCounter extends SignFunction
{

    private $m_Counter = 0;

    public function __construct(&$sign)
    {
        parent::__construct("SignFunctionCounter", $sign);
        if (isset($sign->data["start"]))
        {
            $this->m_Counter = $sign->data["start"];
        }
    }

    public function onTick(int $currentTick)
    {
        if ($currentTick % 20 == 0)// update every seconds
        {
            $this->m_Counter++;
            $this->sign->text[1] = (strval($this->m_Counter));
            $this->sign->updateTexte();
        }
    }

    public function onInterract(Player $player, int $p_Index = -1)
    {
    }
}

