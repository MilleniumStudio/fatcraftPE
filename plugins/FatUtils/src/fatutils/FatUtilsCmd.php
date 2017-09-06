<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 15:22
 */

namespace fatutils;


use fatutils\tools\WorldUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class FatUtilsCmd extends Command implements PluginIdentifiableCommand
{

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin
	{
		FatUtils::getInstance();
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		switch(strtolower($args[0]))
		{
			case "?":
			case "help":
				$sender->sendMessage("/fatUtils");
				$sender->sendMessage("  - help (or ?)");
				$sender->sendMessage("  - getPos");
				break;
			case "getPos":
				if($sender instanceof Player)
					$sender->sendMessage("CurrentLocation: " . WorldUtils::locationToString($sender->getLocation()));
				break;
			default;
		}
	}
}