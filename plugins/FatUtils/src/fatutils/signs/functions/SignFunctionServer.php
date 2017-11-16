<?php

namespace fatutils\signs\functions;

use pocketmine\Player;
use fatcraft\loadbalancer\LoadBalancer;

class SignFunctionServer extends SignFunction
{

    private $type = null;
    private $id = -1;
    private $onlyVIP = false;

    public function __construct(&$sign)
    {
        parent::__construct("SignFunctionServer", $sign);
        if (isset($this->sign->data["type"]) and isset($this->sign->data["id"]))
        {
            $this->type = $this->sign->data["type"];
            $this->id = intval($this->sign->data["id"]);
        }
        else
        {
            throw Exception("SignFunctionServer has no server!");
        }
        if (isset($this->sign->data["onlyVIP"]))
        {
            $this->onlyVIP = $this->sign->data["onlyVIP"];
        }
        $sign->text[0] = (new \fatutils\tools\TextFormatter("template." . $this->type))->asString() . " " . $this->id;
    }

    public function onTick(int $currentTick): bool
    {
        if ($currentTick % 20 == 0)// update every seconds
        {
            $server = LoadBalancer::getInstance()->getNetworkServer($this->type, $this->id);
            $this->sign->text[1] = "";
            $this->sign->text[2] = "";
            $this->sign->text[3] = "";
            if ($server !== null)
            {
                $this->sign->text[1] = "Map: " . $server["name"];
                $players = "§r   " . $server["online"] . "/" . $server["max"];
                if ($server["status"] == "open")
                {
                    $this->sign->text[2] = (new \fatutils\tools\TextFormatter("game.status.open"))->asString() . $players;
                    if ($this->onlyVIP)
                    {
                        $this->sign->text[3] = (new \fatutils\tools\TextFormatter("game.status.joinvip"))->asString();
                    }
                    else
                    {
                        $this->sign->text[3] = (new \fatutils\tools\TextFormatter("game.status.join"))->asString();
                    }
                }
                else if ($server["status"] == "closed")
                {
                    $this->sign->text[2] = (new \fatutils\tools\TextFormatter("game.status.closed"))->asString() . $players;
                }
                else
                {
                    $this->sign->text[2] = $server["status"] . $players;
                }
            }
            else
            {
                $this->sign->text[2] = (new \fatutils\tools\TextFormatter("game.status.offline"))->asString();
            }
            $this->sign->updateTexte();
        }
        return true;
    }

    public function onInterract(Player $player, int $p_Index = -1)
    {
        if ($player->hasPermission("sign.network.serverjoin"))
        {
            LoadBalancer::getInstance()->balancePlayer($player, $this->type, $this->id);
        }
        else
        {
            $player->sendMessage((new \fatutils\tools\TextFormatter("game.status.joinnotnow"))->asStringForPlayer($player));
        }
    }
}

