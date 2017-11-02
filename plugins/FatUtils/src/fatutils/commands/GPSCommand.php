<?php

namespace fatutils\commands;

use fatutils\FatUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class GPSCommand implements CommandExecutor
{
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool
    {
        if ($cmd->getName() ==  "gps")
        {
            if ($sender instanceof Player)
            {
                $sender->sendMessage("Position : " . round($sender->x, 2) . "/" . round($sender->y, 2) . "/" . round($sender->z, 2) . "/" . round($sender->pitch, 2) . "/" . round($sender->yaw, 2));
                FatUtils::getInstance()->getLogger()->info("Position of ". $sender->getName() . " : " . round($sender->x, 2) . "/" . round($sender->y, 2) . "/" . round($sender->z, 2). "/" . round($sender->pitch, 2) . "/" . round($sender->yaw, 2));
            }
        }
        return true;
    }

}
