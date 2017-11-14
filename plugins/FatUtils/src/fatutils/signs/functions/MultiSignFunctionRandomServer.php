<?php

namespace fatutils\signs\functions;

use pocketmine\Player;
use fatcraft\loadbalancer\LoadBalancer;

class MultiSignFunctionRandomServer extends SignFunction
{

    private $type = null;
    private $canJoin = false;

    public function __construct(&$p_MultipleSigns)
    {
        parent::__construct("MultiSignFunctionRandomServer", $p_MultipleSigns);
        if (isset($this->sign->data["type"]))
        {
            $this->type = $this->sign->data["type"];
        }
        else
        {
            throw Exception("MultiSignFunctionRandomServer has no server type !");
        }
        foreach ($p_MultipleSigns->signs as $sign)
        {
            $sign->data["type"] = $this->type;
            $sign->function = new SignFunctionRandomServer($sign);
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
                $sign->text[2] = "";
                $sign->text[3] = "";
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
                    $this->canJoin = false;
                    $sign->text[2] = (new \fatutils\tools\TextFormatter("game.status.noserver"))->asString();
                }
                else
                {
                    $sign->text[1] = $online . "/" . $max;
                    if ($online < $max)
                    {
                        $this->canJoin = true;
                        $sign->text[3] = (new \fatutils\tools\TextFormatter("game.status.joinrandom"))->asString();
                    }
                    else
                    {
                        $this->canJoin = false;
                        $sign->text[2] = (new \fatutils\tools\TextFormatter("game.status.serversfull"))->asString();
                    }
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

