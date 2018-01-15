<?php

/**
 * Created by PhpStorm.
 * User: surva
 * Date: 14.05.16
 * Time: 12:01
 */

namespace surva\allsigns;

use fatutils\players\PlayersManager;
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
                        $tile->setText($configFile->get("commandtext"), $text[1], $text[2], $text[3]);
                        break;

                    case $configFile->get("network"):
                        if ($this->allSigns->getServer()->getPluginManager()->getPlugin("LoadBalancer") != null)
                        {
                            $LoadBalancer = \fatcraft\loadbalancer\LoadBalancer::getInstance();
                            $l_canJoin = false;
                            $l_LastLine = "";
                            if (strstr($text[1], '-')) // in case of server sign
                            {
                                $split = explode('-', $text[1]);
                                $server = $LoadBalancer->getNetworkServer($split[0], $split[1]);
                                if ($server !== null)
                                {
                                    if ($server["status"] == "open")
                                    {
                                        $l_canJoin = true;
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
                                if ($servers !== null and count($servers) > 0)
                                {
                                    $l_canJoin = true;
                                    foreach($servers as $server)
                                    {
                                        $online += $server["online"];
                                        $max += $server["max"];
                                    }
                                    if ($online < $max)
                                    {
                                        $l_canJoin = true;
                                    }
                                }
                                if ($max == 0)
                                {
                                    $l_LastLine = $configFile->get("noserver");
                                }
                                else if ($max == $online)
                                {
                                    $l_LastLine = $configFile->get("serversfull");
                                }
                                $text[2] = $online . "/" . $max;
                            }
                            $tile->setText($configFile->get("networktext"), $text[1], $text[2], $l_canJoin ? $configFile->get("networksignlast") : $l_LastLine);
                        }
                        break;

                    case $configFile->get("tpNextLevel"):
                        $tile->setText($configFile->get("tpNextLevelMessage1"), $configFile->get("tpNextLevelMessage2"), $configFile->get("Obf") . "tp " . $text[1], "");
                        break;

                    case $configFile->get("addCheckpoint"):
                        $tile->setText($configFile->get("Obf") . "checkpoint", $configFile->get("checkpoint"), "", "");
                        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($player);
                        // $player->sendMessage(new TextFormatter("parkour.checkpoint.message"))->asStringForPlayer($l_FatPlayer);
                        // message on checkpoint need to be made
						break;

                    case $configFile->get("endgame"):
                        $location = $block->x."/".$block->y."/".$block->z;
                        $end['money'] = $text[1] != "" ? $text[1] : 0;
                        $end['xp'] = $text[2] != "" ? $text[2] : 0;
                        $end['template'] = $text[3] != "" ? $text[3] : "";
                        // todo create config
                        $configFile->set("rewardsSigns." . $location, $end);
                        $configFile->save();

                        $text[1] = str_replace("{0}", $end['money'], $configFile->get("endgameText2"));
                        $text[2] = str_replace("{0}", $end['xp'], $configFile->get("endgameText3"));
                        $text[3] = str_replace("{0}", $end['template'], $configFile->get("endgameText4"));
                        $tile->setText($configFile->get("endgameText"), $text[1], $text[2], $text[3]);
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
                            if ($player->hasPermission("sign.network.serverjoin"))
                            {
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
                        }
                        break;

                    case $configFile->get("tpNextLevelMessage1"):
                        $command = 'tp ' . $player->getName() . ' ' . substr($text[2], 6);
                        $this->getAllSigns()->getServer()->dispatchCommand($this->m_ConsoleCommandSender, $command);
                        break;

                    case $configFile->get("infoSign"):
                        $tile->setText($configFile->get("Obf") . $configFile->get("infoSign"), $configFile->get("infoSignColor") . $text[1], $configFile->get("Obf") . $text[2], $configFile->get("Obf") . $text[3]);
                        break;

                    case $configFile->get("endgameText"):
                        $location = $block->x."/".$block->y."/".$block->z;
                        if ($configFile->exists("rewardsSigns." . $location))
                        {
                            $reward = $configFile->get("rewardsSigns." . $location);
                            // rewards
                            if ($this->allSigns->getServer()->getPluginManager()->getPlugin("StatsPE") != null)
                            {
								$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($player);
								$l_FatPlayer->addFatsilver($reward["money"]);
								\SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("XP", $player, $reward["xp"]);
                                if ($this->allSigns->getServer()->getPluginManager()->getPlugin("LoadBalancer") != null and \fatcraft\loadbalancer\LoadBalancer::getInstance()->getServerType() != "" and $reward["xp"] != "")
                                {
                                    $l_ServerType = \fatcraft\loadbalancer\LoadBalancer::getInstance()->getServerType();
                                    \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_XP", $player, $reward["xp"]);
                                    \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_played", $player, 1);
                                }
                                $player->sendMessage(str_replace("{0}", $reward['money'], $configFile->get("endgameMessageMoney")));
                                $player->sendMessage(str_replace("{0}", $reward['xp'], $configFile->get("endgameMessageXP")));
                            }
                            // transport to lobby
                            if ($this->allSigns->getServer()->getPluginManager()->getPlugin("LoadBalancer") != null and $reward["template"] != "")
                            {
                                $LoadBalancer = \fatcraft\loadbalancer\LoadBalancer::getInstance();
                                $servers = $LoadBalancer->getServersByType($reward["template"]);
                                if ($servers !== null and count($servers) > 0)
                                {
                                    $this->getAllSigns()->getServer()->dispatchCommand($this->m_ConsoleCommandSender, "server connect " . $player->getName() . " ". $reward["template"]);
                                    $player->sendMessage(str_replace("{0}", $reward["template"], $configFile->get("endgameMessageTransport")));
                                    break;
                                }
                            }
                            $player->sendMessage(str_replace("{0}", "world spawn", $configFile->get("endgameMessageTransport")));
                            $player->teleport($player->getLocation()->level->getSpawnLocation());
                        }
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
