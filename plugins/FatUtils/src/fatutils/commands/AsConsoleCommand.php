<?php

namespace fatutils\commands;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\ban\BanManager;
use fatutils\FatUtils;
use fatutils\shop\ShopItem;
use fatutils\tools\ArrayUtils;
use libasynql\result\MysqlResult;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;


class AsConsoleCommand implements CommandExecutor
{
	public function onCommand(CommandSender $sender, Command $cmd, $p_Label, array $p_Args): bool
    {
        echo("Yo !\n");
        if ($p_Label === "asconsole" )
		{
		    if ($sender instanceof Player)
            {
                if ($sender->isOp())
                {
                    echo("command : " . $cmd . " " . print_r($p_Args, true));
                    LoadBalancer::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), implode(" ", $p_Args));
                }
            }
        }
        return true;
    }

}
