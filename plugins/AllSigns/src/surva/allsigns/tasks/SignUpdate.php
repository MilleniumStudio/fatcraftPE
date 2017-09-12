<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 14.05.16
 * Time: 12:23
 */

namespace surva\allsigns\tasks;

use surva\allsigns\AllSigns;
use pocketmine\level\Level;
use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Sign;

class SignUpdate extends PluginTask {
    /* @var AllSigns */
    private $allSigns;

    public function __construct(AllSigns $allSigns) {
        $this->allSigns = $allSigns;

        parent::__construct($allSigns);
    }

    public function onRun(int $currentTick) {
        foreach($this->getAllSigns()->getServer()->getLevels() as $level) {
            foreach($level->getTiles() as $tile) {
                if($tile instanceof Sign) {
                    $text = $tile->getText();

                    if($text[0] == $this->getAllSigns()->getConfig()->get("worldtext"))
                    {
                        $level = $this->getAllSigns()->getServer()->getLevelByName($text[1]);

                        if($level instanceof Level) {
                            $tile->setText($text[0], $text[1], $text[2], count($level->getPlayers()) . " " . $this->getAllSigns()->getConfig()->get("players"));
                        } else {
                            $tile->setText($text[0], $text[1], $text[2], $this->getAllSigns()->getConfig()->get("error"));
                        }
                    }
                    else if($text[0] == $this->getAllSigns()->getConfig()->get("networktext"))
                    {
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
                                        $text[2] = $this->getAllSigns()->getConfig()->get("serveropen");
                                    }
                                    else if ($server["status"] == "closed")
                                    {
                                        $text[2] = $this->getAllSigns()->getConfig()->get("serverclosed");
                                    }
                                    else
                                    {
                                        $text[2] = $server["status"];
                                    }
                                    $text[2] .= "§r    " . $server["online"] . "/" . $server["max"];
                                }
                                else
                                {
                                    $text[2] = $this->getAllSigns()->getConfig()->get("serveroffline");
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
                            $tile->setText($this->getAllSigns()->getConfig()->get("networktext"), $text[1], $text[2], $this->getAllSigns()->getConfig()->get("networksignlast"));
                        }
                    }
                }
            }
        }
    }

    /**
     * @return AllSigns
     */
    public function getAllSigns(): AllSigns {
        return $this->allSigns;
    }
}