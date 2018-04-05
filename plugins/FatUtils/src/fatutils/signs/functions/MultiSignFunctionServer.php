<?php

namespace fatutils\signs\functions;

use pocketmine\Player;

class MultiSignFunctionServer extends SignFunction
{

    private $type = null;
    private $onlyVIP = false;

    public function __construct(&$p_MultipleSigns)
    {
        parent::__construct("MultiSignFunctionServer", $p_MultipleSigns);
        if (isset($this->sign->data["type"]))
        {
            $this->type = $this->sign->data["type"];
        }
        else
        {
            throw Exception("MultiSignFunctionServer has no server type !");
        }
        if (isset($this->sign->data["onlyVIP"]))
        {
            $this->onlyVIP = $this->sign->data["onlyVIP"];
        }
        $offset = $p_MultipleSigns->data["offset"];
        foreach ($p_MultipleSigns->signs as $sign)
        {
            $sign->data["type"] = $this->type;
            $sign->data["id"] = $sign->sign->namedtag->getInt("Index") + $offset;
            $sign->data["onlyVIP"] = $this->onlyVIP;
            $sign->function = new SignFunctionServer($sign);
        }
    }

    public function onTick(int $currentTick): bool
    {
        foreach ($this->sign->signs as $sign)
        {
            $sign->onTick($currentTick);
        }
        return true;
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

