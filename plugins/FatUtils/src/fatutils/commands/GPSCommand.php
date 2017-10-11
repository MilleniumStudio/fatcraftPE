<?php

namespace fatutils\commands;

use fatutils\FatUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class GPSCommand implements CommandExecutor
{
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool
    {
        if ($cmd->getName() ==  "gps")
        {
            if ($sender instanceof Player)
            {
                $l_Player = $sender;
                $l_Player->sendMessage("Position : " . round($l_Player->x, 2) . "/" . round($l_Player->y, 2) . "/" . round($l_Player->z, 2));
                FatUtils::getInstance()->getLogger()->info("Position of ". $l_Player->getName() . " : " . round($l_Player->x, 2) . "/" . round($l_Player->y, 2) . "/" . round($l_Player->z, 2));
            }
        }
        return true;
    }

}
