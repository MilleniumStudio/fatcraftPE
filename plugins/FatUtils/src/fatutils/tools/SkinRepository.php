<?php

namespace fatutils\tools;

use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\entity\Human;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

use fatutils\FatUtils;

class SkinRepository implements Listener, CommandExecutor
{
    private static $m_Instance = null;
    private $repository = array();
    const DEFAULT_SKIN = "steve";

    public static function getInstance(): SkinRepository
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new SkinRepository();
        return self::$m_Instance;
    }

    private function __construct()
    {
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
        $this->load();
    }

    private function load()
    {
        if (is_dir(FatUtils::getInstance()->getDataFolder() . "skinpacks/"))
        {
            FatUtils::getInstance()->getLogger()->info("[SkinRepository] skinpacks found, loading !");
            foreach(new \IteratorIterator(new \DirectoryIterator(FatUtils::getInstance()->getDataFolder() . "skinpacks/")) as $skinsPack)
            {
                $packName = basename ($skinsPack);
                if ($packName != "." && $packName != "..")
                {
                    if (is_file(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $skinsPack . "/skins.json"))
                    {
                        //extract skins values
                        $skins = new Config(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $skinsPack . "/skins.json", Config::JSON);
                        $globalGeometry = null;
                        if ($skins->exists("geometry"))
                        {
                            $globalGeometry = $skins->get("geometry");
                        }
                        foreach ($skins->get("skins") as $key => $value)
                        {
                            $name = $geometryName = $geometry = $texture = "";
                            $baseCase = false;

                            //name field
                            if (isset($value["localization_name"]))
                                $name = $value["localization_name"];
                            elseif(isset($value["name"])) // special case Steve, Alex, Dummy
                            {
                                $name = $value["name"];
                                $baseCase = true;
                            }
                            else
                                echo $packName . " no name skin\n";

                            //geometry field
                            if (isset($value["geometry"]))
                            {
                                $geometryName = $value["geometry"];
                            }

                            //texture field
                            if (isset($value["texture"]))
                            {
                                if (!$baseCase)
                                {
                                    $texture = $value["texture"];
                                }
                                else
                                {
                                    $texture = substr($value["texture"], strrpos($value["texture"], '/') + 1) . ".png";
                                }
                            }

                            //paid field /!\ if use payd models, client crash !
//                            if (isset($value["type"]))
//                            {
//                                if ($value["type"] === "paid")
//                                {
//                                    continue;
//                                }
//                            }

                            if ($globalGeometry !== null)
                            {
                                $skinsGeometry = new Config(FatUtils::getInstance()->getDataFolder() . $globalGeometry, Config::JSON);
                                $additionnalSkinsGeometry = new Config(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $skinsPack . "/geometry.json", Config::JSON);
                                foreach ($additionnalSkinsGeometry as $key => $values)
                                {
                                    $skinsGeometry->setNested($key, $value);
                                }
                                $geometry = $skinsGeometry->get($geometryName);
                            }
                            else
                            {
                                $skinsGeometry = new Config(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $skinsPack . "/geometry.json", Config::JSON);
                                $geometry = $skinsGeometry->get($geometryName);
                            }
                            if (is_file(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $skinsPack . "/" . $texture))
                            {
                                $skinData = SkinUtils::fromImage(SkinUtils::fromPNG(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $skinsPack . "/" . $texture));
                                $skin = new \pocketmine\entity\Skin(
                                    $name,
                                    $skinData,
                                    "", //cape
                                    $geometryName,
                                    json_encode($geometry)
                                );
                                $this->repository[$name] = $skin;
                            }
                        }
                    }
                    else
                    {
                        echo $packName . " WITHOUT skins.json, please create a readeble file !\n";
//                        if (is_file(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $packName . "/" . $packName . ".json"))
//                        {
//                            echo $packName . ".json found\n";
//                            $skinsGeometry = new Config(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $packName . "/" . $packName . ".json", Config::JSON);
//                            foreach(new \IteratorIterator(new \DirectoryIterator(FatUtils::getInstance()->getDataFolder() . "skinpacks/" . $packName . "/")) as $fileName)
//                            {
//                                echo pathinfo($fileName)['extension'];
//                                if (pathinfo($fileName)['extension'] === "png")
//                                {
//                                    echo $fileName . "\n";
//                                    $skinName = basename ($fileName);
//                                    $geometry = $skinsGeometry->get("geometry." . $packName . "." . $skinName);
//                                    if (is_file($fileName))
//                                    {
//                                        $skinData = SkinUtils::fromImage(SkinUtils::fromPNG($fileName));
//                                        $skin = new \pocketmine\entity\Skin(
//                                            $skinName,
//                                            $skinData,
//                                            "", //cape
//                                            "geometry." . $packName . "." . $skinName,
//                                            json_encode($globalGeometry)
//                                        );
//                                        $this->repository[$name] = $skin;
//                                    }
//                                }
//                            }
//                        }
                    }
                }
            }
            FatUtils::getInstance()->getLogger()->info("[SkinRepository] " . count($this->repository) . " skins loaded !");
        }
    }

    public function getSkins()
    {
        return $this->repository;
    }

    public function getSkin($name)
    {
        if (isset($this->repository[$name]))
            return $this->repository[$name];
        else
            return $this->repository["Steve"];
    }

    public function getRandomSkin() : Skin
    {
        $value = array_rand($this->repository,1);
        $skin = $this->repository[$value];
        if ($skin != null)
            return $skin;
        else
            return $this->repository["Steve"];
    }


    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($args[0]) {
            case "list": {
                $sender->sendMessage("Skin list :");
                foreach ($this->repository as $l_Name => $l_Skin)
                {
                    $sender->sendMessage(" - " . $l_Name);
                }
            }
                break;
            case "get": { //skin get <skin>
                if ($sender instanceof Player) {
                    if (isset($this->repository[$args[1]]))
                    {
                        $sender->setSkin($this->repository[$args[1]]);
                        $sender->sendSkin(FatUtils::getInstance()->getServer()->getOnlinePlayers());
                        $sender->sendMessage("Skin " . $args[1] . " applyed");
                    }
                    else
                    {
                        $sender->sendMessage("Skin " . $args[1] . " not found!");
                    }
                }
            }
            break;
            case "set": { //skin set <entity> <skin>
                if ($sender instanceof Player) {
                    if (isset($this->repository[$args[2]]))
                    {
                        $entity = $sender->level->getEntity(intval($args[1]));
                        if ($entity instanceof Human)
                        {
                            $entity->setSkin($this->repository[$args[2]]);
                            $entity->sendSkin(FatUtils::getInstance()->getServer()->getOnlinePlayers());
                            $sender->sendMessage("Skin " . $args[2] . " applyed on " . $entity->getName());
                        }
                        else
                        {
                            $sender->sendMessage("Entity " . $args[2] . " not instance of Human");
                        }
                    }
                    else
                    {
                        $sender->sendMessage("Skin " . $args[2] . " not found!");
                    }
                }
            }
            break;
        }
        return true;
    }

}

