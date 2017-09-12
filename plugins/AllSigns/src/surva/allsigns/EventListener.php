<?php

/**
 * Created by PhpStorm.
 * User: surva
 * Date: 14.05.16
 * Time: 12:01
 */

namespace surva\allsigns;

use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\command\ConsoleCommandSender;

class EventListener implements Listener
{
    /* @var AllSigns */

    private $allSigns;
    private $m_ConsoleCommandSender;

    public function __construct(AllSigns $allSigns)
    {
        $this->allSigns = $allSigns;
        $this->m_ConsoleCommandSender = new ConsoleCommandSender();
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($block->getId() == Block::SIGN_POST OR $block->getId() == Block::WALL_SIGN)
        {
            $tile = $block->getLevel()->getTile($block);

            if ($tile instanceof Sign)
            {
                $text = $tile->getText();

                $configFile = $this->getAllSigns()->getConfig();

                switch ($text[0])
                {

                    //
                    // FORMATING SIGN
                    //
                    case $configFile->get("world"):
                        $level = $this->getAllSigns()->getServer()->getLevelByName($text[1]);

                        if ($level instanceof Level)
                        {
                            $tile->setText($configFile->get("worldtext"), $text[1], $text[2], count($level->getPlayers()) . " " . $this->getAllSigns()->getConfig()->get("players"));
                        }
                        else
                        {
                            $block->getLevel()->setBlock($block, Block::get(Block::AIR));

                            $player->sendMessage($configFile->get("noworld"));
                        }
                        break;

                    case $configFile->get("command"):
                        $tile->setText($configFile->get("networktext"), $text[1], $text[2], $text[3]);
                        break;

                    case $configFile->get("network"):
                        if ($this->allSigns->getServer()->getPluginManager()->getPlugin("LoadBalancer") != null)
                        {
                            $LoadBalancer = \fatcraft\loadbalancer\LoadBalancer::getInstance();
                            if (strstr($text[1], '-')) // in case of server sign
                            {
                                $split = explode('-', $text[1]);
                                $server = $LoadBalancer->getNetworkServer($split[0], $split[1]);
                                if ($server !== null)
                                {
                                    if ($server["status"] == "open")
                                    {
                                        $text[2] = $configFile->get("serveropen");
                                    }
                                    else if ($server["status"] == "closed")
                                    {
                                        $text[2] = $configFile->get("serverclosed");
                                    }
                                    else
                                    {
                                        $text[2] = $server["status"];
                                    }
                                    $text[2] .= "§r    " . $server["online"] . "/" . $server["max"];
                                }
                                else
                                {
                                    $text[2] = $configFile->get("serveroffline");
                                }
                            }
                            else // in case of type sign
                            {
                                $online = 0;
                                $max = 0;
                                $servers = $LoadBalancer->getServersByType($text[1]);
                                foreach($servers as $server)
                                {
                                    $online += $server["online"];
                                    $max += $server["max"];
                                }
                                $text[2] = $online . "/" . $max;
                            }
                            $tile->setText($configFile->get("networktext"), $text[1], $text[2], $configFile->get("networksignlast"));
                        }
                        break;

                    case $configFile->get("tpNextLevel"):
                        $tile->setText($configFile->get("tpNextLevelMessage1"), $configFile->get("tpNextLevelMessage2"), $configFile->get("Obf") . "tp " . $text[1], "");
                        break;

                    case $configFile->get("addCheckpoint"):
                        $tile->setText($configFile->get("Obf") . "checkpoint", $configFile->get("checkpoint"), "", "");
                        break;

                    case $configFile->get("goToGame"):
                        $tile->setText($configFile->get("Obf") . $text[1], $configFile->get("goToGameMessage1"), $configFile->get("goToGameMessage2"), $configFile->get("Obf") . $text[2]);
                        break;

                    case $configFile->get("goToLobby"):
                        $tile->setText($configFile->get("Obf") . $text[1], $configFile->get("goBackLobbyMessage1"), $configFile->get("goBackLobbyMessage2"), $configFile->get("Obf") . $text[2]);
                        break;

                    case $configFile->get("worldtext"):
                        $level = $this->getAllSigns()->getServer()->getLevelByName($text[1]);

                        if ($level instanceof Level)
                        {
                            $player->teleport($level->getSafeSpawn());
                        }
                        else
                        {
                            $player->sendMessage($configFile->get("noworld"));
                        }
                        break;

                    //
                    // INTERACT SIGN
                    //
                    case $configFile->get("commandtext"):
                        $this->getAllSigns()->getServer()->dispatchCommand($player, $text[2] . $text[3]);
                        break;

                    case $configFile->get("networktext"):
                        if ($this->allSigns->getServer()->getPluginManager()->getPlugin("LoadBalancer") != null)
                        {
                            $LoadBalancer = \fatcraft\loadbalancer\LoadBalancer::getInstance();
                            if (strstr($text[1], '-')) // in case of server sign
                            {
                                $split = explode('-', $text[1]);
                                $server = $LoadBalancer->getNetworkServer($split[0], $split[1]);
                                if ($server !== null)
                                {
                                    if ($server["status"] == "open")
                                    {
                                        $this->getAllSigns()->getServer()->dispatchCommand($this->m_ConsoleCommandSender, "server connect " . $player->getName() . " ". $split[0] . " " . $split[1]);
                                    }
                                }
                            }
                            else // in case of type sign
                            {
                                $this->getAllSigns()->getServer()->dispatchCommand($this->m_ConsoleCommandSender, "server connect " . $player->getName() . " ". $text[1]);
                            }
                        }
                        break;

                    case $configFile->get("tpNextLevelMessage1"):
                        $command = 'tp ' . $player->getName() . ' ' . substr($text[2], 6);
                        $this->getAllSigns()->getServer()->dispatchCommand($this->m_ConsoleCommandSender, $command);
                        break;

                    case $configFile->get("infoSign"):
                        $tile->setText($configFile->get("Obf") . $configFile->get("infoSign"), $configFile->get("infoSignColor") . $text[1], $configFile->get("Obf") . $text[2], $configFile->get("Obf") . $text[3]);
                        break;
                    case $configFile->get("Obf") . $configFile->get("infoSign"):
                        $command = substr($text[2], 3) . ' ' . $player->getName() . ' ' . substr($text[3], 3);
                        $this->getAllSigns()->getServer()->dispatchCommand($this->m_ConsoleCommandSender, $command);
                        var_dump(substr($text[2], 3));
                        var_dump(substr($text[3], 3));
                        break;

                    default:
                        if ($text[1] == $configFile->get("checkpoint"))
                        {
                            $command = "spawnpoint " . $player->getName() . " " . $player->getPosition()->getX() . " " . $player->getPosition()->getY() . " " . $player->getPosition()->getZ();
                            $this->getAllSigns()->getServer()->dispatchCommand($this->m_ConsoleCommandSender, $command);
                        }

                        if ($text[1] == $configFile->get("goToGameMessage1") || $text[1] == $configFile->get("goBackLobbyMessage1"))
                        {
                            $command = substr($text[0], 3) . " " . $player->getName() . " " . substr($text[3], 3);
                            var_dump($command);
                            $this->getAllSigns()->getServer()->dispatchCommand($this->m_ConsoleCommandSender, $command);
                        }
                }
            }
        }
    }

    /**
     * @return AllSigns
     */
    public function getAllSigns(): AllSigns
    {
        return $this->allSigns;
    }

}
