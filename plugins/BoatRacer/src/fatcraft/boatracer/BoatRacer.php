<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 13:51
 */

namespace fatcraft\boatracer;

use fatutils\FatUtils;
use fatutils\tools\WorldUtils;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\entity\Entity;
use pocketmine\entity\Vehicle;
use pocketmine\entity\Boat as BoatEntity;
use pocketmine\event\entity\EntityVehicleExitEvent;
use pocketmine\event\entity\EntityVehicleEnterEvent;

class BoatRacer extends PluginBase implements Listener
{
    private static $m_Instance;

    public static function getInstance(): BoatRacer
    {
        return self::$m_Instance;
    }

    public function onLoad()
    {
        self::$m_Instance = $this;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
//        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        WorldUtils::stopWorldsTime();
    }

    public function onPlayerJoin(PlayerJoinEvent $p_Event)
    {
        $p_Event->getPlayer()->sendMessage("You are autamaticaly on a boat !");
        $this->applyBoat($p_Event->getPlayer());
    }

    // disable dismount
    public function onEntityVehicleExit(EntityVehicleExitEvent $p_Event)
    {
        echo "EntityVehicleExitEvent\n";
        if ($p_Event->getEntity() instanceof Player){
//            if (!$p_Event->getEntity()->isOp())
//            {
                $p_Event->setCancelled(true);
                $p_Event->geVehicle()->kill();
                $this->applyBoat($p_Event->getEntity());
                $p_Event->getEntity()->sendMessage("You can't get out !");
//            }
        }
    }

    public function onEntityVehicleEnter(EntityVehicleEnterEvent $p_Event)
    {
        echo "EntityVehicleEnterEvent\n";
        if ($p_Event->getEntity() instanceof Player)
        {
//            $p_Event->getEntity()->setInvulnerable(true);
            //
        }
    }

    public function applyBoat(Player $p_Player)
    {
        $nbt = new CompoundTag("", [
            new ListTag("Pos", [
                new DoubleTag("", $p_Player->getX() + 0.5),
                new DoubleTag("", $p_Player->getY() + 0.0),
                new DoubleTag("", $p_Player->getZ() + 0.5)
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

        if ($p_Player->getLevel() !== null)
        {
            return;
        }
        $entity = Entity::createEntity(BoatEntity::NETWORK_ID, $p_Player->getLevel(), $nbt);
//        $entity->mountEntity($p_Player);
        new \fatutils\tools\DelayedExec(function() use (&$entity, &$p_Player)
        {
            if ($entity instanceof Vehicle)
            {
                $entity->mountEntity($p_Player);
            }
        }, 5);
    }

    // disable all inventory items move
//    public function onInventoryTransaction(InventoryTransactionEvent $p_Event)
//    {
//        $p_Event->setCancelled(true);
//    }

    public function onItemPickup(InventoryPickupItemEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onArrowPickup(InventoryPickupArrowEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

//    public function onPlayerDropItem(PlayerDropItemEvent $p_Event)
//    {
//        $p_Event->setCancelled(true);
//    }

    public function onPlayerExhaust(PlayerExhaustEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onPlayerDamage(EntityDamageEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
            $e->setCancelled(true);
    }
}