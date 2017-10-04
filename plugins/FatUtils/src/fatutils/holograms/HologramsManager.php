<?php

namespace fatutils\holograms;

use Exception;
use pocketmine\Server;
use fatutils\FatUtils;
use pocketmine\utils\Config;

/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 13/09/2017
 * Time: 11:58
 */
class HologramsManager
{

    private static $instance;
    public $plugin;
    public $task;
    public $holograms;

    public static function add(Hologram $hologram)
    {
        if (HologramsManager::$instance == null) new HologramsManager();
        HologramsManager::$instance->holograms[] = $hologram;
    }

    public static function getInstance(): HologramsManager
    {
        if (HologramsManager::$instance == null) new HologramsManager();
        return HologramsManager::$instance;
    }

    private function __construct()
    {
        HologramsManager::$instance = $this;
        $this->plugin = FatUtils::getInstance();
        $this->loadConfigs();
    }

    public function loadConfigs()
    {
        $this->plugin->saveResource("holograms.yml");
        $config = new Config(FatUtils::getInstance()->getDataFolder() . "holograms.yml");
        if ($config == null)
        {
            return;
        }
        foreach ($config->get("holograms") as $key => $value)
        {
            $name = isset($value['name']) ? $value['name'] : null;
            if ($name == null)
            {
                throw new Exception("Holograms Error: you need to specify a name");
            }
            $location = isset($value['location']) ? $value['location'] : null;
            if ($location == null)
            {
                throw new Exception("Holograms Error: you need to specify a location");
            }
            $location = explode("/", $location);
            if (Server::getInstance()->getLevelByName($location[0]) == null)
            {
                throw new Exception("Holograms Error: world " . $location[0] . " not found");
            }
            $title = isset($value['title']) ? $value['title'] : null;
            $text = isset($value['text']) ? $value['text'] : null;
            new Hologram($name, $location[1], $location[2], $location[3], Server::getInstance()->getLevelByName($location[0]), $title, $text);
        }
    }
}