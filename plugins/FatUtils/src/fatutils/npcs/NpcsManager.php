<?php

namespace fatutils\npcs;

use fatutils\FatUtils;
use fatutils\pets\PetTypes;
use fatutils\tools\WorldUtils;
use fatutils\tools\schedulers\LoopedExec;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
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
        new LoopedExec([$this, "updateNpcs"]);
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
            $displayname = (new \fatutils\tools\TextFormatter($displayname))->asString();
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
//            $effects = isset($value['effects']) ? $value['effects'] : [];
            $function = isset($value['function']) ? $value['function'] : null;
            $data = isset($value['data']) ? $value['data'] : [];
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

                $entity->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, $size);
                if($entity instanceof Human)
                {
                    if (isset($equipment["head"]) && $equipment["head"] !== "")
                    {
                        $entity->getArmorInventory()->setHelmet(\fatutils\tools\ItemUtils::getItemFromRaw($equipment["head"]));
                    }
                    if (isset($equipment["chest"]) && $equipment["chest"] !== "")
                    {
                        $entity->getArmorInventory()->setChestplate(\fatutils\tools\ItemUtils::getItemFromRaw($equipment["chest"]));
                    }
                    if (isset($equipment["pants"]) && $equipment["pants"] !== "")
                    {
                        $entity->getArmorInventory()->setLeggings(\fatutils\tools\ItemUtils::getItemFromRaw($equipment["pants"]));
                    }
                    if (isset($equipment["boots"]) && $equipment["boots"] !== "")
                    {
                        $entity->getArmorInventory()->setBoots(\fatutils\tools\ItemUtils::getItemFromRaw($equipment["boots"]));
                    }
                    if (isset($equipment["held"]) && $equipment["held"] !== "")
                    {
                        $entity->getInventory()->setItemInHand(\fatutils\tools\ItemUtils::getItemFromRaw($equipment["held"]));
                    }
                }

                $entity->data = $data;
                $entity->equipment = $equipment;
                try
                {
                    switch ($function)
                    {
                        case "NPCFunctionTeleport":
                            $entity->function = new functions\NPCFunctionTeleport($entity);
                            break;
                        case "NPCFunctionCounter":
                            $entity->function = new functions\NPCFunctionCounter($entity);
                            break;
                        case "NPCFunctionShop":
                            $entity->function = new functions\NPCFunctionShop($entity);
                            break;
                        case "NPCFunctionKits":
                            $entity->function = new functions\NPCFunctionKits($entity);
							break;
                        default:
                            break;
                    }
                } catch (Exception $ex)
                {
                    FatUtils::getInstance()->getLogger()->warning("[NPCS] ". $ex->getMessage());
                }

                /*if ($update)
                {
                    $entity->namedtag->Update = true;
                }*/
                $this->updateNPC($entity);
            }
        }
        $this->updateNpcs();
    }

    public function spawnNpc(Location $p_Location, $chosenType, $name, $displayname, array $commands = [], Skin $p_Skin = null): Entity
    {
        $nbt = new CompoundTag("", [
            "Pos" => new ListTag("Pos", [
                new DoubleTag("", $p_Location->getX()),
                new DoubleTag("", $p_Location->getY()),
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

        $nbt->setByte("Update", true);

        if ($chosenType === "Player")
        {
            if ($p_Skin == null or $p_Skin == "")
            {
                $p_Skin = \fatutils\tools\SkinRepository::getInstance()->getSkin("Steve");
            }
            $nbt->setTag(new CompoundTag("Skin", ["Data" => new StringTag("Data", $p_Skin->getSkinData()), "Name" => new StringTag("Name", $p_Skin->getSkinId())]));

            $entity = new \fatutils\pets\HumanPet($p_Location->getLevel(), $nbt, $chosenType);
        }
        else
        {
            $entity = new \fatutils\pets\CustomPet($p_Location->getLevel(), $nbt, $chosenType);
        }

        $entity->namedtag->setString("Command", "");
        if (isset($commands[0]))
            $entity->namedtag->setString("Command", $commands[0]);
        $entity->getDataPropertyManager()->setString(Entity::DATA_NAMETAG, $name);
        $entity->setNameTag($displayname);
        WorldUtils::forceLoadChunk($p_Location);
        $p_Location->getLevel()->addEntity($entity);
        $this->m_RegisteredNPCS[$name] = $entity;
        $entity->spawnToAll();
        FatUtils::getInstance()->getLogger()->info("[NPCS] Spawned entity " . $entity->getId() . " !");
        return $entity;
    }

    public function updateNpcs()
    {
        $currentTick = FatUtils::getInstance()->getServer()->getTick();
        foreach ($this->m_RegisteredNPCS as $l_Npc)
        {
            $l_Npc->onTick($currentTick);
        }
    }

    public function updateNPC(Entity $p_Entity)
    {
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
                case "reload": {
                    
                }
                    break;
            }
        } else {
            echo "Commands only available as a player\n";
        }
        return true;
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
        if(!$event->getDamager() instanceof Player || $event->getEntity() instanceof Player) {
            return;
        }
        if($event->getEntity()->getDataPropertyManager()->getString(Entity::DATA_NAMETAG) !== null)
        {
            if(isset($this->m_RegisteredNPCS[$event->getEntity()->getDataPropertyManager()->getString(Entity::DATA_NAMETAG)]))
            {
                $entity = $this->m_RegisteredNPCS[$event->getEntity()->getDataPropertyManager()->getString(Entity::DATA_NAMETAG)];
                $entity->onInterract($event->getDamager());
                $event->setCancelled(true);
            }
        }
    }
}