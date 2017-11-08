<?php

namespace fatutils\signs\functions;

use pocketmine\Player;

class SignFunctionMultiServer extends SignFunction
{

    private $type = null;

    public function __construct(&$p_MultipleSigns)
    {
        parent::__construct("SignFunctionMultiServer", $p_MultipleSigns);
        if (isset($this->sign->data["type"]))
        {
            $this->type = $this->sign->data["type"];
        }
        else
        {
            throw Exception("SignFunctionServer has no server type !");
        }
        $offset = $p_MultipleSigns->data["offset"];
        foreach ($p_MultipleSigns->signs as $sign)
        {
            $sign->data["type"] = $this->type;
            $sign->data["id"] = $sign->sign->namedtag->index + $offset;
            $sign->function = new SignFunctionServer($sign);
        }
    }

    public function onTick(int $currentTick)
    {
        foreach ($this->sign->signs as $sign)
        {
            $sign->onTick($currentTick);
        }
    }

    public function onInterract(Player $player, int $p_Index = -1)
    {
        if ($p_Index != -1)
        {
            if (isset($this->sign->signs[$p_Index]))
            {
                $this->sign->signs[$p_Index]->onInterract($player);
            }
        }
    }
}

