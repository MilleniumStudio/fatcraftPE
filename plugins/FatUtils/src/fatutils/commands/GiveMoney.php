<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 11/10/2017
 * Time: 11:41
 */

namespace fatutils\commands;


use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use libasynql\DirectQueryMysqlTask;
use libasynql\result\MysqlResult;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class GiveMoney implements CommandExecutor
{
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool
    {
        if(!isset($args[0]))
            return false;
        switch ($args[0]) {
            case "giveFG":{
                if(isset($args[1])){
                    if(isset($args[2])) {
                        $fatPlayer = PlayersManager::getInstance()->getFatPlayerByName($args[1]);
                        if($fatPlayer != null) {

                            $fatPlayer->addFatgold(intval($args[2]));
                            $sender->sendMessage($args[2]." were given to '".$args[1]."'");
                            $fatPlayer->getPlayer()->sendMessage("You just get ".$args[2]." fatgolds");
                        }
                        else
                        {
                            $sender->sendMessage("Can't find player ".$args[1]);
                            $sender->sendMessage("Trying give money to " . $args[2] . " in db with xuid...");
                            $sender->sendMessage("xuid : " . $args[1]);

                            $result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                                "SELECT * FROM players WHERE xuid = ?", [
                                    ["s", $args[1]]
                                ]);
                            if ($result instanceof \libasynql\result\MysqlSelectResult)
                            {
                                if (count($result->rows) == 1)
                                {
                                    $l_fatGold = $result->rows[0]["fatgold"];

                                    MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(), "UPDATE players SET fatgold = ? WHERE xuid = ?", [
                                        ["s", $l_fatGold + intval($args[2])],
                                        ["s", $args[1]]
                                    ]);

                                    FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
                                        new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                                            "INSERT INTO shop_history (uuid, name, item, spentFS, spentFG) VALUES (?, ?, ?, ?, ?)", [
                                                ["s", "xuid:".$args[1]],
                                                ["s", "name:".$args[2] . " FG"],
                                                ["s", $args[2]],
                                                ["i", -2],
                                                ["i", -2]
                                            ]
                                        ));
                                }
                            }
                        }
                    }else{
                        $sender->sendMessage("You have to specify the ammount to give to this player");
                    }
                }else{
                    $sender->sendMessage("You have to specify the targeted player");
                }
            }break;
            case "giveFS":{
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
            default: {
                $sender->sendMessage("Error, your argument doesn't exist");
                return false;
            }
        }
        return true;
    }
}