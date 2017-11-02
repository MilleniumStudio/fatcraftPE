<?php

namespace fatutils\npcs;

use fatutils\FatUtils;
use fatutils\pets\PetTypes;
use fatutils\tools\WorldUtils;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Item\Item;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteTag;

class NpcsManager implements Listener, CommandExecutor
{
    private static $m_Instance = null;
    public $config;
    private $m_RegisteredNPCS = array();

    public static function getInstance(): NpcsManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new NpcsManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new OnTick(FatUtils::getInstance()), 1);
        $this->loadConfigs();
    }

    public function loadConfigs()
    {
        FatUtils::getInstance()->getLogger()->info("[NPCS] Loading npcs.yml");
        FatUtils::getInstance()->saveResource("npcs.yml");
        $this->config = new Config(FatUtils::getInstance()->getDataFolder() . "npcs.yml");
        if ($this->config == null)
        {
            return;
        }
        foreach ($this->config->get("npcs") as $key => $value)
        {
            $name = isset($value['name']) ? $value['name'] : null;
            if ($name == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[NPCS] Error: npc without name");
                continue;
            }
            $displayname = isset($value['displayname']) ? $value['displayname'] : null;
            if ($displayname == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[NPCS] Error: npc without displayname");
                continue;
            }
            $p_RawLocation = isset($value['location']) ? $value['location'] : null;
            if ($p_RawLocation == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[NPCS] Error: npc ". $name . " has no/bad location");
                continue;
            }
            $l_Location = WorldUtils::stringToLocation($p_RawLocation);
            if ($l_Location->level == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[NPCS] Error: npc ". $name . " world " . $p_RawLocation . " not found");
                continue;
            }
            $type = isset($value['type']) ? $value['type'] : null;
            if ($type == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[NPCS] Error: npc without type");
                continue;
            }

            //Optionnal
            $size = (float) (isset($value['size']) ? $value['size'] : 1);
            $skin = isset($value['skin']) ? $value['skin'] : null;
            $equipment = isset($value['equipment']) ? $value['equipment'] : [];
            $update = isset($value['update']) ? $value['update'] : false;
            $effects = isset($value['effects']) ? $value['effects'] : [];
            $commands = isset($value['commands']) ? $value['commands'] : [];

            if (array_key_exists($type, PetTypes::ENTITIES))
            {
                $entitySkin = null;
                if ($type === "Player")
                {
                    if ($skin != null && $skin !== "")
                    {
                        $entitySkin = \fatutils\tools\SkinRepository::getInstance()->getSkin($skin);
                    }
                }

                $entity = $this->spawnNpc($l_Location, $type, $name, $displayname, $commands, $entitySkin);
                $entity->setDataProperty(Entity::DATA_SCALE, Entity::DATA_TYPE_FLOAT, $size);

                if($entity instanceof Human)
                {
                    if (isset($equipment["head"]) && $equipment["head"] !== "")
                    {
                        $entity->getInventory()->setHelmet(Item::fromString($equipment["head"]));
                    }
                    if (isset($equipment["chest"]) && $equipment["chest"] !== "")
                    {
                        $entity->getInventory()->setChestplate(Item::fromString($equipment["chest"]));
                    }
                    if (isset($equipment["pants"]) && $equipment["pants"] !== "")
                    {
                        $entity->getInventory()->setLeggings(Item::fromString($equipment["pants"]));
                    }
                    if (isset($equipment["boots"]) && $equipment["boots"] !== "")
                    {
                        $entity->getInventory()->setItemInHand(Item::fromString($equipment["boots"]));
                    }
                    if (isset($equipment["held"]) && $equipment["held"] !== "")
                    {
                        $entity->getInventory()->setItemInHand(Item::fromString($equipment["held"]));
                    }
                }

                if ($update)
                {
                    $entity->namedtag->Update = true;
                }
                $this->updateNPC($entity);
            }
        }
    }

    public function spawnNpc(Location $p_Location, $chosenType, $name, $displayname, array $commands = [], Skin $p_Skin = null): Entity
    {
        $nbt = new CompoundTag("", [
            "Pos" => new ListTag("Pos", [
                new DoubleTag("", $p_Location->getX()),
                new DoubleTag("", $p_Location->getY() + 0.5),
                new DoubleTag("", $p_Location->getZ())
            ]),
            "Motion" => new ListTag("Motion", [
                new DoubleTag("", 0),
                new DoubleTag("", 0),
                new DoubleTag("", 0)
            ]),
            "Rotation" => new ListTag("Rotation", [
                new FloatTag("", $p_Location->yaw),
                new FloatTag("", $p_Location->pitch)
            ])
        ]);
        $nbt->Commands = new CompoundTag("Commands", []);
        $nbt->Update = new ByteTag("Update", false);

        if ($chosenType === "Player")
        {
            if ($p_Skin == null or $p_Skin == "")
            {
                $p_Skin = \fatutils\tools\SkinRepository::getInstance()->getSkin("Steve");
            }
            $nbt->Skin = new CompoundTag("Skin", ["Data" => new StringTag("Data", $p_Skin->getSkinData()), "Name" => new StringTag("Name", $p_Skin->getSkinId())]);
            $entity = new \fatutils\pets\HumanPet($p_Location->getLevel(), $nbt, $chosenType);
        }
        else
        {
            $entity = new \fatutils\pets\CustomPet($p_Location->getLevel(), $nbt, $chosenType);
        }

        foreach ($commands as $command)
        {
            $entity->namedtag->Commands[$command] = new StringTag($command, $command);
        }
        $this->m_RegisteredNPCS[$name] = $entity;
        $entity->setDataProperty(Entity::DATA_FLAG_NO_AI, Entity::DATA_TYPE_BYTE, 1, true);
        $entity->setNameTagVisible(true);
        $entity->setNameTagAlwaysVisible(true);
        $p_Location->getLevel()->addEntity($entity);
        $entity->spawnToAll();
        FatUtils::getInstance()->getLogger()->info("[NPCS] Spawned entity " . $entity->getId() . " !");
        return $entity;
    }

    public function updateNpcs(int $currentTick)
    {
//        if ($currentTick % 60)
//        {
//            foreach ($this->m_RegisteredNPCS as $l_Entity)
//            {
//                if(isset($l_Entity->namedtag->Update))
//                {
//                    $this->updateNPC($l_Entity);
//                }
//            }
//        }
    }

    public function updateNPC(Entity $p_Entity)
    {
        FatUtils::getInstance()->getLogger()->info("[NPCS] update " . $p_Entity->getId());
        $p_Entity->sendData($p_Entity->getViewers());
    }

    /**
     * @param \pocketmine\command\CommandSender $sender
     * @param \pocketmine\command\Command $command
     * @param string $label
     * @param string[] $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
            switch ($args[0]) {
                case "spawn": {
                    $entity = $this->spawnNpc($sender, $args[1], $args[2], $args[2]);
                    $sender->sendMessage("NPC " . $entity->getId() . " spawned !");
                }
                    break;
                case "rm": {
                }
                    break;
                case "tp": {
                }
                    break;
                case "list": {
                    $sender->sendMessage("NPC list :");
                    foreach ($this->m_RegisteredNPCS as $l_Entity)
                    {
                        $sender->sendMessage(" - " . $l_Entity->getId() . " " . $l_Entity->getName() . " (pos : " . WorldUtils::locationToString($l_Entity) . ")");
                    }
                }
                    break;
                case "stop": {
                }
                    break;
            }
        } else {
            echo "Commands only available as a player\n";
        }
        return true;
    }

    public function onPlayerPeLoginEvent(\pocketmine\event\player\PlayerPreLoginEvent $p_Event)
    {
//        \fatutils\tools\SkinUtils::saveSkin($p_Event->getPlayer()->getSkin(), FatUtils::getInstance()->getDataFolder() . "skins/" . $p_Event->getPlayer()->getName() . ".png");
    }

    /**
        * @param EntityDamageEvent $event
        * @ignoreCancelled true
        *
        * @return void
        */
    public function onEntityDamage(EntityDamageEvent $event)
    {
        if(!$event instanceof EntityDamageByEntityEvent) {
            return;
        }
        if(!$event->getDamager() instanceof Player) {
            return;
        }
        if(isset($event->getEntity()->namedtag->Commands))
        {
            $event->setCancelled(true);
            foreach ($event->getEntity()->namedtag->Commands as $cmd) {
                FatUtils::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $event->getDamager()->getName(), $cmd));
            }
        }
    }

//    public static function getPlayerSkinFromPNG(string $o_Resource) :Skin
//    {
//        $geometryJsonEncoded = base64_decode($packet->clientData["SkinGeometry"] ?? "");
//        if($geometryJsonEncoded !== ""){
//            $geometryJsonEncoded = json_encode(json_decode($geometryJsonEncoded));
//        }
//
//        $skin = new Skin(
//            $packet->clientData["SkinId"],
//            base64_decode($packet->clientData["SkinData"] ?? ""),
//            base64_decode($packet->clientData["CapeData"] ?? ""),
//            $packet->clientData["SkinGeometryName"],
//            $geometryJsonEncoded
//        );
//        return $skin;
//    }
}

//===============================================
class OnTick extends PluginTask
{
    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        NpcsManager::getInstance()->updateNpcs($currentTick);
    }
}