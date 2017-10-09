<?php

namespace fatutils\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class FirestormCommand extends PluginBase implements CommandExecutor
{

    public function __construct()
    {
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool
    {
        if ($sender instanceof \pocketmine\Player)
        {
            $l_Player = $sender;
            $l_Player->sendMessage("Play me!");
        }
        return true;
    }

}
