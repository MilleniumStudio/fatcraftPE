<?php

namespace fatutils\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

use fatutils\players\PlayersManager;

class FirestormCommand extends PluginBase implements CommandExecutor
{

    public function __construct()
    {
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool
    {
        if ($sender instanceof \pocketmine\Player)
        {
            if (count($args) == 1)
            {
                if (\fatutils\tools\StringUtils::isEmailValid($args[0]))
                {
                    if (PlayersManager::getInstance()->fatPlayerExist($sender))
                    {
                        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($sender);
                        if ($l_FatPlayer->getFSAccount() == null)
                        {
                            $l_FatPlayer->setFSAccount($args[0]);
                            $sender->sendMessage("Firestorm account successfully linked !");
                        }
                        else
                        {
                            $sender->sendMessage("Firestorm account already linked !");
                        }
                    }
                }
                else
                {
                    $sender->sendMessage("You must enter Firestorm email account.");
                }
            }
            else
            {
                $sender->sendMessage("usage : \"/fs account@email.com\" to link a Firestorm account.");
            }
        }
        return true;
    }

}
