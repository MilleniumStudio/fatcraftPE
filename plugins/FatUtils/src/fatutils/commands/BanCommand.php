<?php

namespace fatutils\commands;

use fatutils\FatUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use fatutils\ui\impl\LanguageWindow;
use fatutils\players\PlayersManager;

/**
 * Class BanCommand
 * @package fatutils\commands
 *
 * /ban <playerName> [<duration> month|days|hours|minutes]
 */

class BanCommand implements CommandExecutor
{
	public function __construct()
	{
	}

	public function onCommand(CommandSender $sender, Command $cmd, $p_Label, array $p_Args): bool
    {
        var_dump("MyBanCmd", $p_Label, $p_Args);

		if ($p_Label === "ban")
		{
			echo "plop\n";
		} else if ($p_Label === "banip")
		{
			echo "plop2\n";
		}

        return true;
    }

}
