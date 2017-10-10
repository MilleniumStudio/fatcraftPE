<?php

namespace fatutils\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class GPSCommand extends PluginBase implements CommandExecutor
{

    public function __construct()
    {
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool
    {
        if ($cmd->getName() ==  "gps")
        {
            if ($sender instanceof \pocketmine\Player)
            {
                $l_Player = $sender;
                $l_Player->sendMessage("Position : " . round($l_Player->x, 2) . "/" . round($l_Player->y, 2) . "/" . round($l_Player->z, 2));
                \fatutils\FatUtils::getInstance()->getLogger()->info("Position of ". $l_Player->getName() . " : " . round($l_Player->x, 2) . "/" . round($l_Player->y, 2) . "/" . round($l_Player->z, 2));
            }
        }
        return true;
    }

}
