<?php

namespace fatutils\holograms;

use Exception;
use pocketmine\level\Location;
use pocketmine\utils\Config;
use fatutils\FatUtils;
use fatutils\tools\WorldUtils;

class HologramsManager
{

    private static $instance;
    public $config;
    public $holograms;

    public static function getInstance(): HologramsManager
    {
        if (HologramsManager::$instance == null)
        {
            new HologramsManager();
        }
        return HologramsManager::$instance;
    }

    private function __construct()
    {
        HologramsManager::$instance = $this;
        $this->loadConfigs();
    }

    public function loadConfigs()
    {
        FatUtils::getInstance()->getLogger()->info("[Holograms] Loading holograms.yml");
        FatUtils::getInstance()->saveResource("holograms.yml");
        $this->config = new Config(FatUtils::getInstance()->getDataFolder() . "holograms.yml");
        if ($this->config == null)
        {
            return;
        }
        foreach ($this->config->get("holograms") as $key => $value)
        {
            $name = isset($value['name']) ? $value['name'] : null;
            if ($name == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Holograms] Error: hologram without name");
                continue;
            }
            $p_RawLocation = isset($value['location']) ? $value['location'] : null;
            if ($p_RawLocation == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Holograms] Error: hologram ". $name . " has no location");
                continue;
            }
            $l_Location = WorldUtils::stringToLocation($p_RawLocation);
            if ($l_Location->level == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Holograms] Error: hologram ". $name . " world " . $p_RawLocation . " not found");
                continue;
            }
            $title = isset($value['title']) ? $value['title'] : null;
            $text = isset($value['text']) ? $value['text'] : null;
            new Hologram($name, $l_Location, $text, $title);
            FatUtils::getInstance()->getLogger()->info("[Holograms] hologram ". $name . " spawned on " . $p_RawLocation);
        }
    }

    public function add(Hologram $hologram)
    {
        HologramsManager::$instance->holograms[$hologram->name] = $hologram;
    }

    public function newHologram(Location $l_Location, String $p_Name, string $p_Title = "", string $p_Text = "")
    {
        $this->add(new Hologram($p_Name, $l_Location, $p_Title, $p_Text));
        FatUtils::getInstance()->getLogger()->info("[Holograms] Hologram " . $p_Name . " created on " . WorldUtils::locationToString($l_Location));
    }

    public function getHologram(string $p_Name): Hologram
    {
        return HologramsManager::$instance->holograms[$p_Name];
    }
}