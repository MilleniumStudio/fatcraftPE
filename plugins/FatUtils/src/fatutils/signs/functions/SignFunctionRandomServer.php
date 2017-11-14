<?php

namespace fatutils\signs\functions;

use pocketmine\Player;
use fatcraft\loadbalancer\LoadBalancer;

class SignFunctionRandomServer extends SignFunction
{

    private $type = null;
    private $canJoin = false;

    public function __construct(&$sign)
    {
        parent::__construct("SignFunctionRandomServer", $sign);
        if (isset($this->sign->data["type"]))
        {
            $this->type = $this->sign->data["type"];
        }
        else
        {
            throw Exception("SignFunctionRandomServer has no server!");
        }
        $sign->text[0] = (new \fatutils\tools\TextFormatter("template." . $this->type))->asString();
    }

    public function onTick(int $currentTick): bool
    {
        if ($currentTick % 20 == 0)// update every seconds
        {
            $this->sign->text[1] = "";
            $this->sign->text[2] = "";
            $this->sign->text[3] = "";
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
                $this->sign->text[2] = (new \fatutils\tools\TextFormatter("game.status.noserver"))->asString();
            }
            else
            {
                $this->sign->text[1] = $online . "/" . $max;
                if ($online < $max)
                {
                    $this->canJoin = true;
                    $this->sign->text[3] = (new \fatutils\tools\TextFormatter("game.status.joinrandom"))->asString();
                }
                else
                {
                    $this->canJoin = false;
                    $this->sign->text[2] = (new \fatutils\tools\TextFormatter("game.status.serversfull"))->asString();
                }
            }
            $this->sign->updateTexte();
        }
        return true;
    }

    public function onInterract(Player $player, int $p_Index = -1)
    {
        LoadBalancer::getInstance()->balancePlayer($player, $this->type);
    }
}

