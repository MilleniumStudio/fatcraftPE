<?php

namespace fatutils\signs\functions;

use pocketmine\Player;

class SignFunctionCounter extends SignFunction
{

    private $m_Counter = 0;
    private $m_Stop = -1;

    public function __construct(&$sign)
    {
        parent::__construct("SignFunctionCounter", $sign);
        if (isset($sign->data["start"]))
        {
            $this->m_Counter = $sign->data["start"];
        }
        if (isset($sign->data["stop"]))
        {
            $this->m_Stop = $sign->data["stop"];
        }
    }

    public function onTick(int $currentTick)
    {
        if ($currentTick % 20 == 0)// update every seconds
        {
            if ($this->m_Counter < $this->m_Stop)
            {
                $this->sign->text[2] = "";
                $this->m_Counter++;
                $this->sign->text[1] = (strval($this->m_Counter));
                $this->sign->updateTexte();
            }
            else
            {
                $this->sign->text[2] = "Tap to restart !";
                $this->sign->updateTexte();
            }
        }
    }

    public function onInterract(Player $player, int $p_Index = -1)
    {
        $this->m_Counter = $this->sign->data["start"];
    }
}

