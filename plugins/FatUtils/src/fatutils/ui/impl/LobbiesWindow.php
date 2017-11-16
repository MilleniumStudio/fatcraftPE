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

        $l_Servers = LoadBalancer::getInstance()->getServers(LoadBalancer::TEMPLATE_TYPE_LOBBY, LoadBalancer::SERVER_STATE_OPEN);

        if (!is_null($l_Servers))
		{
			foreach ($l_Servers as $l_Server)
			{
                            $thisServer = $l_Server["id"] !== LoadBalancer::getInstance()->getServerId();
				$l_Window->addPart((new Button())
					->setText(($thisServer ? (TextFormat::GREEN . "✔ " . TextFormat::RESET . TextFormat::DARK_GRAY) : "") . (new TextFormatter("template.lobby"))->asStringForPlayer($p_Player) . " " . $l_Server["id"] . " (" . $l_Server["online"] . "/" . $l_Server["max"] . " players)")
					->setCallback(function () use ($l_FatPlayer, $thisServer, $l_Server)
					{
                                            if (!$thisServer)
                                            {
						LoadBalancer::getInstance()->transferPlayer($l_FatPlayer->getPlayer(), $l_Server["ip"], $l_Server["port"], "plop");
                                            }
					})
				);
			}
		}

        $l_Window->open();
    }
}

