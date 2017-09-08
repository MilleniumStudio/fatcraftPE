<?php

namespace fatcraft\loadbalancer;

use pocketmine\Player;
use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;

class BalancePlayerEvent extends PlayerEvent implements Cancellable
{

    public static $handlerList;
    private $plugin;
    private $ip;
    private $port;

    public function __construct(LoadBalancer $plugin, Player $player, $ip, $port)
    {
        $this->player = $player;
        $this->plugin = $plugin;
        $this->ip = $ip;
        $this->port = $port;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

}
