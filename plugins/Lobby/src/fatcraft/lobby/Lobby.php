<?php

namespace fatcraft\lobby;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;

class Lobby extends PluginBase implements Listener
{

    private static $m_Instance;

    public function onLoad()
    {
        // registering instance
        LoadBalancer::$m_Instance = $this;
    }

    public function onEnable()
    {
        // register events listener
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("Enabled");
    }

    public function onDisable()
    {

    }

    public static function getInstance(): Lobby
    {
        return LoadBalancer::$m_Instance;
    }

    /**
     * @param PlayerJoinEvent $p_Event
     *
     * @priority HIGH
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $p_Event)
    {

    }

    public function onPlayerQuitEvent(PlayerQuitEvent $p_Event)
    {

    }

}
