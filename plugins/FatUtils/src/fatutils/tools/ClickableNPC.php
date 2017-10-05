<?php
/**
 * User: Unikaz
 * Date: 11/09/2017
 */

namespace fatutils\tools;

use fatutils\FatUtils;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use ReflectionObject;

class ClickableNPC implements Listener
{
    public $villager;
    private $m_OnHitCallback = null;

    public function __construct(Location $p_location)
    {
        $tag = new CompoundTag("", [
                "Pos" => new ListTag("Pos", [
                    new DoubleTag("", $p_location->getX()),
                    new DoubleTag("", $p_location->getY()),
                    new DoubleTag("", $p_location->getZ())
                ]),
                "Motion" => new ListTag("Motion", [
                    new DoubleTag("", 0),
                    new DoubleTag("", 0),
                    new DoubleTag("", 0)
                ]),
                "Rotation" => new ListTag("Rotation", [
                    new FloatTag("", 90),
                    new FloatTag("", 0)
                ])
            ]
        );
        $this->villager = new Villager($p_location->level, $tag);
        $p_location->getLevel()->addEntity($this->villager);
        $this->villager->spawnToAll();

        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
    }

    /**
     * @param callable(void|Player) $p_OnHit
     */
    public function setOnHitCallback(Callable $p_OnHit)
    {
        $this->m_OnHitCallback = $p_OnHit;
    }

    public function onEntityDamageEvent(EntityDamageEvent $event)
    {
        $target = $event->getEntity();
        if ($target instanceof Villager && $target === $this->villager) {
            if ($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();
                if ($damager instanceof Player) {
                    $event->setCancelled();
                    if (is_callable($this->m_OnHitCallback))
                    {
                        $params = (new ReflectionObject((object)$this->m_OnHitCallback))->getMethod('__invoke')->getParameters();
                        if (count($params) == 0)
                            ($this->m_OnHitCallback)();
                        if (count($params) == 1)
                            ($this->m_OnHitCallback)($damager);

                    }
                }
            }
        }
    }
}