<?php

namespace fatcraft\digger;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\Boat as BoatEntity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;

class DataDigger extends PluginBase implements Listener
{

    private static $m_Instance;

    public function onLoad()
    {
        // registering instance
        DataDigger::$m_Instance = $this;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable()
    {
    }

    public static function getInstance(): DataDigger
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

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $p_Param): bool
    {
        if ($cmd->getName() === "test")
        {
            if (count($p_Param) >= 1)
            {
                switch ($p_Param[0])
                {
                    case "byte": //test byte 1 true
                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_BYTE, $p_Param[2] === 'true' ? 1 : 0);
                        break;
                    case "short": //test short 1 1
                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_SHORT, intval($p_Param[2]));
                        break;
                    case "int": //test int 1 1
                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_INT, intval($p_Param[2]));
                        break;
                    case "float": //test float 1 1.1
                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_FLOAT, floatval($p_Param[2]));
                        break;
                    case "string": //test string 1 test
                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_STRING, $p_Param[2]);
                        break;
                    case "slot": //test slot 1 1
//                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_LONG, intval($p_Param[2]));
                        break;
                    case "pos": //test pos 1 x y z
                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_POS, array(intval($p_Param[2]), intval($p_Param[3]), intval($p_Param[4])));
                        break;
                    case "long": //test long 1 1
                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_LONG, intval($p_Param[2]));
                        break;
                    case "vector": //test vector 1 x y z
                        $sender->setDataProperty(intval($p_Param[1]), Entity::DATA_TYPE_VECTOR3F, array(floatval($p_Param[2]), floatval($p_Param[3]), floatval($p_Param[4])));
                        break;
                    //
                    case "entity": //test vector 1 x y z
                        $nbt = new CompoundTag("", [
                            new ListTag("Pos", [
                                new DoubleTag("", $sender->getX() + 0.5),
                                new DoubleTag("", $sender->getY()),
                                new DoubleTag("", $sender->getZ() + 0.5)
                                    ]),
                            new ListTag("Motion", [
                                new DoubleTag("", 0),
                                new DoubleTag("", 0),
                                new DoubleTag("", 0)
                                    ]),
                            new ListTag("Rotation", [
                                new FloatTag("", lcg_value() * 360),
                                new FloatTag("", 0)
                                    ]),
                        ]);
                        $entity = Entity::createEntity(BoatEntity::NETWORK_ID, $sender->level, $nbt);
                        $sender->getServer()->getLogger()->debug("Entity " . $entity->getId() . " spawned !");
                        $entity->mountEntity($sender);
                        break;
                    default:
                        //
                        break;
                }
            }
        }
        elseif ($cmd->getName() === "debug")
        {
            switch ($p_Param[0])
            {
                case "entity":
                    if ($sender instanceof Player)
                    {
                        if (count($p_Param) == 2) //debug entity <distance> -> return the entity ID you are looking at in the specified distance
                        {
                            $radius = intval($p_Param[1]);
                            if ($radius > 10)
                            {
                                $sender->sendMessage("Radius limit to 10 !");
                                return true;
                            }
                            $entity = $sender->getEntityLookingAt($radius);
                            if ($entity !== null)
                            {
                                $sender->sendMessage("Entity looking at : " . $entity->getId());
                            }
                            else
                            {
                                $sender->sendMessage("No entity in radius !");
                            }
                        }
                        elseif (count($p_Param) == 3) //debug entity <entity_id>
                        {
                            //
                        }
                        elseif (count($p_Param) == 4) //debug entity <entity_id> <param> <value>
                        {
                            $entity = $sender->level->getEntity(intval($p_Param[1]));
                            switch ($p_Param[2])
                            {
                                case "x": //debug entity <entity_id> x <yaw>
                                    $entity->x = floatval($p_Param[3]);
                                    $sender->sendMessage("Entity x set to " . floatval($p_Param[3]));
                                    break;
                                case "y": //debug entity <entity_id> y <pitch>
                                    $entity->y = floatval($p_Param[3]);
                                    $sender->sendMessage("Entity y set to " . floatval($p_Param[3]));
                                    break;
                                case "z": //debug entity <entity_id> z <pitch>
                                    $entity->z = floatval($p_Param[3]);
                                    $sender->sendMessage("Entity z set to " . floatval($p_Param[3]));
                                    break;
                                case "yaw": //debug entity <entity_id> yaw <yaw>
                                    $entity->yaw = floatval($p_Param[3]);
                                    $sender->sendMessage("Entity yaw set to " . floatval($p_Param[3]));
                                    break;
                                case "pitch": //debug entity <entity_id> pitch <pitch>
                                    $entity->pitch = floatval($p_Param[3]);
                                    $sender->sendMessage("Entity pitch set to " . floatval($p_Param[3]));
                                    break;
                                case "speed": //debug entity <entity_id> speed <pitch>
                                    $entity->speed = intval($p_Param[3]);
                                    $sender->sendMessage("Entity speed set to " . intval($p_Param[3]));
                                    break;
                                case "gravity": //debug entity <entity_id> gravity <pitch>
                                    $entity->gravity = floatval($p_Param[3]);
                                    $sender->sendMessage("Entity gravity set to " . floatval($p_Param[3]));
                                    break;
                                case "drag": //debug entity <entity_id> drag <pitch>
                                    $entity->drag = floatval($p_Param[3]);
                                    $sender->sendMessage("Entity drag set to " . floatval($p_Param[3]));
                                    break;
                            }
                        }
                    }
                    break;
            }
        }
        elseif ($cmd->getName() === "world")
        {
            switch ($p_Param[0])
            {
                case "list":
                    $sender->sendMessage("worlds :");
                    foreach ($this->getServer()->getLevels() as $level)
                    {
                        $sender->sendMessage(" - " . $level->getId() . " " . $level->getName());
                    }
                    break;
                case "dump":
                    $level = $this->getServer()->getLevel(intval($p_Param[1]));
                    if ($level !== null)
                    {
                        $sender->sendMessage("world " . $level->getName() . " leveldata : ");
                        $result = print_r($level->getProvider()->getLevelData(), TRUE);
                        $sender->sendMessage($result);
                    }
                    else
                    {
                        $sender->sendMessage("world " . $p_Param[1] . " not found !");
                    }
                    break;
                case "load":
                    $level = $this->getServer()->getLevel(intval($p_Param[1]));
                    if ($level !== null)
                    {
                        if (count($p_Param) == 4)
                        {
                            $task = $level->getProvider()->requestChunkTask(intval($p_Param[2]), intval($p_Param[3]));
                            $this->getServer()->getScheduler()->scheduleAsyncTask($task);
                        }
                        else
                        {
                            $sender->sendMessage("syntax : /world load <map> <chunkX> <chunkZ>");
                        }
                    }
                    else
                    {
                        $sender->sendMessage("world " . $p_Param[1] . " not found !");
                    }
                    break;
            }
        }
        return true;
    }

}
