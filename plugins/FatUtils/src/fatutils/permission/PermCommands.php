<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 11/10/2017
 * Time: 11:41
 */

namespace fatutils\permission;


use fatutils\players\PlayersManager;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class PermCommands implements CommandExecutor
{
    public function __construct()
    {
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool
    {
        if(!isset($args[0]))
            return false;
        switch ($args[0]) {
            case "setGroup":{
                if(isset($args[1])){
                    if(isset($args[2])) {
                        $fatPlayer = PlayersManager::getInstance()->getFatPlayerByName($args[1]);
                        if($fatPlayer != null) {
                            $fatPlayer->setPermissionGroup($args[2]);
                            PermissionManager::getInstance()->updatePermissions($fatPlayer);
                            $sender->sendMessage($args[1]." is now '".$args[2]."'");
                            $fatPlayer->getPlayer()->sendMessage("Your group change to ".$args[2]."");
                        }
                        else
                            $sender->sendMessage("Can't find player ".$args[1]);
                    }else{
                        $sender->sendMessage("You have to specify the group you want to set to this player");
                    }
                }else{
                    $sender->sendMessage("You have to specify the targeted player");
                }
            }break;
            case "getGroup":{
                if(isset($args[1])){
                    $fatPlayer = PlayersManager::getInstance()->getFatPlayerByName($args[1]);
                    if($fatPlayer != null)
                        $sender->sendMessage($fatPlayer->getPermissionGroup());
                    else
                        $sender->sendMessage("Can't find player ".$args[1]);

                }else{
                    $sender->sendMessage("You have to specify the targeted player");
                }
            }break;
            case "listPerm":{
                if(isset($args[1])){
                    $sender->sendMessage(PermissionManager::getInstance()->listPerms($args[1]));
                }else{
                    $sender->sendMessage("You have to specify the targeted group");
                }
            }break;
            case "reload":{
                PermissionManager::getInstance()->loadFromConfig();
                foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                    PermissionManager::getInstance()->updatePermissions(PlayersManager::getInstance()->getFatPlayer($p));
                }
                $sender->sendMessage("Permissions reloaded");
            }break;
            default: {
                $sender->sendMessage("Error, your argument doesn't exist");
                return false;
            }
        }
        return true;
    }
}