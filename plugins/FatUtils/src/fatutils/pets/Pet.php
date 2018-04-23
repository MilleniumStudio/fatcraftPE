<?php

namespace fatutils\pets;

use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\shop\ShopItem;
use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 17/10/2017
 * Time: 14:11
 */
class Pet extends ShopItem
{
    /** @var  FatPlayer $m_fatPlayer */
    private $m_fatPlayer;
    private $m_petTypes;
    private $m_options;


    /** @var Location $m_nextPosition */
    private $m_nextPosition = null;
    /** @var  CustomPet $m_CustomPet */
    private $m_CustomPet;

    //todo debug
    static $nbCat = 0;

    public function getSlotName(): string
    {
        return ShopItem::SLOT_PET;
    }

    public function equip()
    {
        $this->m_fatPlayer = PlayersManager::getInstance()->getFatPlayer($this->getEntity());

        foreach (LoadBalancer::getInstance()->getServer()->getLevel(1)->getEntities() as $l_entity)
        {
            if ($l_entity instanceof Player || $l_entity->getOwningEntity() == null)
                continue;

            // don't return if the current $l_entity is the current legit equiped pet
            if ($this->m_fatPlayer->getSlot(ShopItem::SLOT_PET)->getEntity()->getId() == $l_entity->getId())
                continue;

            // prevent spawning multiple preview pets
            if ($l_entity->getOwningEntityId() == $this->getEntity()->getId())
                return;
        }
        $this->m_petTypes = $this->getDataValue("type", "Parrot");
        $this->m_options = $this->getDataValue("options", array_key_exists("options", PetTypes::ENTITIES[$this->m_petTypes])?PetTypes::ENTITIES[$this->m_petTypes]["options"]:[]);
        $this->m_nextPosition = $this->m_fatPlayer->getPlayer()->getLocation();


        $tag = new CompoundTag("", [
                "Pos" => new ListTag("Pos", [
                    new DoubleTag("", $this->m_fatPlayer->getPlayer()->getLocation()->getX()),
                    new DoubleTag("", $this->m_fatPlayer->getPlayer()->getLocation()->getY()),
                    new DoubleTag("", $this->m_fatPlayer->getPlayer()->getLocation()->getZ())
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

        $this->m_CustomPet = new CustomPet($this->m_fatPlayer->getPlayer()->getLevel(), $tag, $this->m_petTypes, $this->m_options);
        $this->m_CustomPet->setOwningEntity($this->m_fatPlayer->getPlayer());
        $this->m_CustomPet->getDataPropertyManager()->setByte(Entity::DATA_FLAG_NO_AI, 1);
        $this->m_fatPlayer->getPlayer()->getLocation()->getLevel()->addEntity($this->m_CustomPet);
        $this->m_CustomPet->spawnToAll();
    }

    public function unequip()
    {
        $this->m_CustomPet->kill();
        $this->m_CustomPet->flagForDespawn();
        //$this->m_fatPlayer->emptySlot(ShopItem::SLOT_PET);
    }

    public function getCustomPet()
    {
        return $this->m_CustomPet;
    }

    public function updatePosition()
    {
        if ($this->m_CustomPet == null) {
            echo "entity is null \n";
            FatUtils::getInstance()->getLogger()->debug("[Pet] custom pet is null !");
            return;
        }
        if ($this->m_CustomPet->level == null)
        {
            return;
        }
        if (!$this->m_CustomPet->isAlive())
        {
            if ($this->m_fatPlayer->getSlot(ShopItem::SLOT_PET) == null)
                return;
            FatUtils::getInstance()->getLogger()->debug("[Pet] " . $this->m_CustomPet->getId() . " is dead, reviving !");
            $this->m_CustomPet->setHealth(20);
        }

        $playerPos = $this->m_fatPlayer->getPlayer()->getLocation()->add(0, $this->m_CustomPet->m_offsetY, 0);
        $petPos = $this->m_CustomPet->getLocation();
        $dist = $petPos->distance($playerPos->asVector3());
        if ($dist > 15) {
            $this->m_CustomPet->teleport($this->m_fatPlayer->getPlayer());
            $this->m_CustomPet->spawnToAll();
        } elseif ($dist > 3 + $this->m_CustomPet->m_distOffset) {
            // calculate move vector
            $vec = new Vector3($playerPos->getX() - $petPos->getX(), $playerPos->getY() - $petPos->getY(), $playerPos->getZ() - $petPos->getZ());
            $vec = $vec->normalize()->multiply($this->m_CustomPet->m_speed * 2);
            // check if it needs to jump to climb a block
            $frontPosVec = $this->m_CustomPet->getLocation()->asVector3()->add($this->m_CustomPet->getDirectionVector()->asVector3()->multiply(0.3 + $this->m_CustomPet->width / 2))->add(0, 0, 0);
            $frontBlockId = $this->m_CustomPet->level->getBlock($frontPosVec)->getId();
            if ($this->m_CustomPet->isOnGround() || $this->m_CustomPet->m_climb || !$this->m_CustomPet->m_hasGravity) {
                if ($frontBlockId != BlockIds::AIR && $frontBlockId != BlockIds::WATER) {
                    $vec->y = 0.9;
                } else if ($this->m_CustomPet->m_isJumper) {// if the mob is a jumper
                    $vec->y = 0.3;
                }
            }
            // calculate yaw
            $vecOr = new Vector3(0, 0, 1);
            $yaw = rad2deg(atan2($vec->getZ(), $vec->getX()) - atan2($vecOr->getZ(), $vecOr->getX())) % 360;
            // apply everything
            $this->m_CustomPet->setRotation($yaw, 0);
            $this->m_CustomPet->setMotion($vec);
        } elseif (!$this->m_CustomPet->m_hasGravity) {
            //to slowdown flyers, and avoid that they turn around the player for nothing
            $this->m_CustomPet->motionX *= 0.5;
            $this->m_CustomPet->motionY *= 0.5;
            $this->m_CustomPet->motionZ *= 0.5;
        }
    }
}