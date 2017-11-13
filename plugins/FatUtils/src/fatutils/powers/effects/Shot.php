<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 08/11/2017
 * Time: 17:45
 */

namespace fatutils\powers\effects;


use fatutils\powers\APower;
use fatutils\tools\particles\ParticleBuilder;
use pocketmine\item\Item;
use pocketmine\level\particle\Particle;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class Shot extends APower
{
    /*
     *  the ray is based on the boat yaw, cause we don't have the player's yaw when in a boat...
     */

    function getIcon(): Item
    {
        return \pocketmine\item\ItemFactory::get(369);
    }

    function action(): bool
    {
        $this->destroy();
        /** @var Position $loc */
        $loc = $this->owner->vehicle->asPosition();
        $vec = new Vector3(cos(deg2rad($this->owner->vehicle->yaw)), 0, sin(deg2rad($this->owner->vehicle->yaw)));
        for ($i = 0; $i < 10; $i++) {
            $loc = Position::fromObject($loc->add($vec), $loc->level);
            ParticleBuilder::fromParticleId(Particle::TYPE_REDSTONE)->play($loc);
            $entities = $loc->level->getNearbyEntities(new AxisAlignedBB($loc->x - 0.5, $loc->y - 0.5, $loc->z - 0.5, $loc->x + 0.5, $loc->y + 0.5, $loc->z + 0.5));
            foreach ($entities as $entity) {
                if($entity !== $this->owner && $entity !== $this->owner->vehicle) {
                    $entity->setMotion(new Vector3(0, 1, 0));
                }
            }
        }
        return true;
    }
}