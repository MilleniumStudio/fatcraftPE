<?php

namespace fatcraft\lobby\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use fatcraft\lobby\Lobby;

class MenuCommand implements CommandExecutor
{

    public function __construct(Lobby $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args): bool
    {
        switch (strtolower($cmd->getName()))
        {
            case "menu":
                if (isset($args[0]))
                {
                    $args[0] = strtolower($args[0]);
//                    if ($args[0] === "button")
//                    {
//                        WindowsManager::getInstance()->sendMenu($sender, WindowsManager::WINDOW_BUTTON_MENU);
//                    }
//                    if ($args[0] === "input")
//                    {
//                        WindowsManager::getInstance()->sendMenu($sender, WindowsManager::WINDOW_INPUT_MENU);
//                    }
//                    if ($args[0] === "modal")
//                    {
//                        WindowsManager::getInstance()->sendMenu($sender, WindowsManager::WINDOW_MODAL_MENU);
//                    }
                    if ($args[0] === "test")
                    {
                        \fatutils\holograms\HologramsManager::getInstance()->newHologram($sender, $args[1], $args[1], $args[1]);
                    }
                }
        }
        return true;
    }

}
