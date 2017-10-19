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
                        
                        break;
                }
            }
        }
        return true;
    }

}
