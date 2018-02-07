<?php

namespace fatutils\commands;

use fatutils\ban\BanManager;
use fatutils\FatUtils;
use fatutils\tools\ArrayUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\PluginTask;

/**
 * Class BanCommand
 * @package fatutils\commands
 *
 * /ban <playerName> [<duration> month|days|hours|minutes]
 */

class BanCommand implements CommandExecutor
{
	public function onCommand(CommandSender $sender, Command $cmd, $p_Label, array $p_Args): bool
    {
        if (($p_Label === "ban" && $sender->hasPermission("ban.uuid")) || ($p_Label === "banip" && $sender->hasPermission("ban.ip")))
		{
            $p_Player = FatUtils::getInstance()->getServer()->getPlayer($p_Args[0]);
			if (!is_null($p_Player))
			{
				$l_ExpirationTime = null;
				$l_Reason = "";

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
				if (isset($l_ArgsParsed["cause"]))
					$l_Reason = implode(" ", $l_ArgsParsed["cause"]);

				if ($p_Label === "ban")
					BanManager::getInstance()->banUuid($p_Player->getUniqueId(), $l_ExpirationTime, $l_Reason);
				else
					BanManager::getInstance()->banIp($p_Player->getAddress(), $l_ExpirationTime, $l_Reason);

				$p_Player->kick($l_Reason);
			}
		}
        return true;
    }

}
