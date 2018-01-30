<?php

namespace fatutils\ui\impl;

use fatcraft\loadbalancer\LoadBalancer;
use pocketmine\Player;

use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\players\PlayersManager;
use fatutils\players\FatPlayer;
use pocketmine\utils\TextFormat;

class LobbiesWindow
{
    public function __construct(Player $p_Player)
    {
        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("lobby.window.title"))->asStringForPlayer($p_Player));

        $l_Servers = LoadBalancer::getInstance()->getServersByType(LoadBalancer::TEMPLATE_TYPE_LOBBY);

        if (!is_null($l_Servers))
		{
            $serverNbr = count($l_Servers);
            $serverId = 1;
            $addedServerCount = 0;
            while (true)
            {
                foreach ($l_Servers as $l_Server)
                {
                    if ($l_Server["id"] == $serverId)
                    {
                        $thisServer = intval($l_Server["id"]) == intval(LoadBalancer::getInstance()->getServerId());
                        $l_Window->addPart((new Button())
                            ->setText(($thisServer ? (TextFormat::GREEN . "✔ " . TextFormat::RESET . TextFormat::DARK_GRAY) : "") . (new TextFormatter("template.lobby"))->asStringForPlayer($p_Player) . " " . $l_Server["id"] . " (" . $l_Server["online"] . "/" . $l_Server["max"] . " players)")
                            ->setCallback(function () use ($l_FatPlayer, $thisServer, $l_Server) {
                                if (!$thisServer && ($l_Server["online"] < $l_Server["max"])) {
                                    LoadBalancer::getInstance()->transferPlayer($l_FatPlayer->getPlayer(), $l_Server["ip"], $l_Server["port"], "plop");
                                }
                            })
                        );
                        $addedServerCount++;
                    }
                }
                $serverId++;
                if ($addedServerCount == $serverNbr ||
                    $serverId >= 100) // just in case... to prevent infinite loop
                    break;
            }
		}

        $l_Window->open();
    }
}

