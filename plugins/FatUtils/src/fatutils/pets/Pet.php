<?php

namespace fatutils\pets;

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
    /** @var  CustomPet $m_entity */
    private $m_entity;

    //todo debug
    static $nbCat = 0;

    public function getSlotName(): string
    {
        return ShopItem::SLOT_PET;
    }

    public function equip()
    {
        $this->m_fatPlayer = PlayersManager::getInstance()->getFatPlayer($this->getPlayer());
        $this->m_petTypes = $this->getDataValue("type", "Parrot");
        $this->m_options = $this->getDataValue("options", []);
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

        $this->m_entity = new CustomPet($this->m_fatPlayer->getPlayer()->getLevel(), $tag, $this->m_petTypes, $this->m_options);

        $this->m_entity->setDataProperty(Entity::DATA_FLAG_NO_AI, Entity::DATA_TYPE_BYTE, 1, true);

        $this->m_fatPlayer->getPlayer()->getLocation()->getLevel()->addEntity($this->m_entity);
        $this->m_entity->spawnToAll();
    }

    public function unequip()
    {
        $this->m_entity->kill();
//        $this->m_fatPlayer->setSlot(ShopItem::FAT_PLAYER_SHOP_SLOT_PET, null);
    }

    public function getEntity()
    {
        return $this->m_entity;
    }

    public function updatePosition()
    {
        if ($this->m_entity == null) {
            echo "entity is null \n";
            return;
        }

        $playerPos = $this->m_fatPlayer->getPlayer()->getLocation()->add(0, $this->m_entity->m_offsetY, 0);
        $petPos = $this->m_entity->getLocation();
        $dist = $petPos->distance($playerPos->asVector3());
        if ($dist > 15) {
            $this->m_entity->teleport($this->m_fatPlayer->getPlayer());
            $this->m_entity->spawnToAll();
        } elseif ($dist > 3 + $this->m_entity->m_distOffset) {
            // calculate move vector
            $vec = new Vector3($playerPos->getX() - $petPos->getX(), $playerPos->getY() - $petPos->getY(), $playerPos->getZ() - $petPos->getZ());
            $vec = $vec->normalize()->multiply($this->m_entity->m_speed * 2);
            // check if it needs to jump to climb a block
            $frontPosVec = $this->m_entity->getLocation()->asVector3()->add($this->m_entity->getDirectionVector()->asVector3()->multiply(0.3 + $this->m_entity->width / 2))->add(0, 0, 0);
            $frontBlockId = $this->m_entity->level->getBlock($frontPosVec)->getId();
            if ($this->m_entity->isOnGround() || $this->m_entity->m_climb || !$this->m_entity->m_hasGravity) {
                if ($frontBlockId != BlockIds::AIR && $frontBlockId != BlockIds::WATER) {
                    $vec->y = 0.9;
                } else if ($this->m_entity->m_isJumper) {// if the mob is a jumper
                    $vec->y = 0.3;
                }
            }
            // calculate yaw
            $vecOr = new Vector3(0, 0, 1);
            $yaw = rad2deg(atan2($vec->getZ(), $vec->getX()) - atan2($vecOr->getZ(), $vecOr->getX())) % 360;
            // apply everything
            $this->m_entity->setRotation($yaw, 0);
            $this->m_entity->setMotion($vec);
        } elseif (!$this->m_entity->m_hasGravity) {
            //to slowdown flyers, and avoid that they turn around the player for nothing
            $this->m_entity->motionX *= 0.5;
            $this->m_entity->motionY *= 0.5;
            $this->m_entity->motionZ *= 0.5;
        }
    }
}