<?php

namespace fatutils\commands;

use fatutils\ban\BanManager;
use fatutils\FatUtils;
use fatutils\tools\ArrayUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use fatutils\ui\impl\LanguageWindow;
use fatutils\players\PlayersManager;

/**
 * Class MuteCommand
 * @package fatutils\commands
 *
 * /mute <playerName> [for <duration> month|days|hours|minutes]
 */
class MuteCommand implements CommandExecutor
{
	public function onCommand(CommandSender $sender, Command $cmd, $p_Label, array $p_Args): bool
	{
		if (count($p_Args) >= 1)
		{
			$l_Player = FatUtils::getInstance()->getServer()->getPlayer($p_Args[0]);
			if (!is_null($l_Player))
			{
				if (($p_Label === "mute" && $sender->hasPermission("chat.mute")))
				{
					$l_ExpirationTime = 5 * 60;

					$l_ArgsParsed = ArrayUtils::parseCmd(["for", "cause"], $p_Args);
					if (isset($l_ArgsParsed["for"]) && is_numeric($l_ArgsParsed["for"][0]))
					{
						$l_ExpirationTime = intval($l_ArgsParsed["for"][0]);
						if (count($l_ArgsParsed["for"]) > 1)
						{
							$l_Unit = strtolower($l_ArgsParsed["for"][1]);
							if ($l_Unit === "d" || $l_Unit === "day" || $l_Unit === "days")
								$l_ExpirationTime = $l_ExpirationTime * 60 * 60 * 24;
							else if ($l_Unit === "h" || $l_Unit === "hour" || $l_Unit === "hours")
								$l_ExpirationTime = $l_ExpirationTime * 60 * 60;
							else if ($l_Unit === "m" || $l_Unit === "min" || $l_Unit === "mins" || $l_Unit === "minute" || $l_Unit === "minutes")
								$l_ExpirationTime = $l_ExpirationTime * 60;
						}
					}

					PlayersManager::getInstance()->getFatPlayer($l_Player)->setMuted($l_ExpirationTime);

					$sender->sendMessage("Muted " . $l_Player->getName());
				} else if ($p_Label === "unmute" && $sender->hasPermission("chat.unmute"))
				{
					PlayersManager::getInstance()->getFatPlayer($l_Player)->setMuted(0);
					$sender->sendMessage("Unmute " . $l_Player->getName());
				}
			}
		}

		return true;
	}

}
