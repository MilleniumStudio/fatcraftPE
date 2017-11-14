<?php

namespace fatutils\signs\functions;

use pocketmine\Player;
use fatcraft\loadbalancer\LoadBalancer;

class MultiSignFunctionTeleport extends SignFunction
{

    private $to = null;
    private $type = null;

    public function __construct(&$p_MultipleSigns)
    {
        parent::__construct("MultiSignFunctionTeleport", $p_MultipleSigns);
        if (isset($this->sign->data["to"]) && isset($this->sign->data["type"]))
        {
            $this->to = $this->sign->data["to"];
            $this->type = $this->sign->data["type"];
        }
        else
        {
            throw Exception("MultiSignFunctionTeleport has no destination !");
        }
        foreach ($p_MultipleSigns->signs as $sign)
        {
            $sign->data["to"] = $this->to;
            $sign->function = new SignFunctionTeleport($sign);
        }
    }

    public function onTick(int $currentTick): bool
    {
        if ($currentTick % 20 == 0)// update every seconds
        {
            foreach ($this->sign->signs as $sign)
            {
                $sign->text[0] = (new \fatutils\tools\TextFormatter("template." . $this->type))->asString();
                $sign->text[1] = "";
                $sign->text[2] = (new \fatutils\tools\TextFormatter("game.status.gotoroom1"))->asString();
                $sign->text[3] = (new \fatutils\tools\TextFormatter("game.status.gotoroom2"))->asString();
                $online = 0;
                $max = 0;
                $servers = LoadBalancer::getInstance()->getServersByType($this->type);
                if ($servers !== null and count($servers) > 0)
                {
                    foreach($servers as $server)
                    {
                        $online += $server["online"];
                        $max += $server["max"];
                    }
                }
                if ($max == 0)
                {
                    $sign->text[1] = (new \fatutils\tools\TextFormatter("game.status.noserver"))->asString();
                }
                else
                {
                    $sign->text[1] = $online . "/" . $max;
                }
                $sign->updateTexte();
            }
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

