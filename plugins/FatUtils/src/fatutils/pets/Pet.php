<?php

namespace fatutils\pets;

use fatutils\players\FatPlayer;
use fatutils\shop\ShopItem;
use pocketmine\entity\Entity;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
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


    /** @var Location $m_nextPosition */
    private $m_nextPosition = null;
    /** @var  Entity $m_entity */
    private $m_entity;


    public function __construct(FatPlayer $player, $petTypes)
    {
        $this->m_fatPlayer = $player;
        $this->m_petTypes = $petTypes;
        $this->m_nextPosition = $player->getPlayer()->getLocation();
    }


    public function getSlotName(): string
    {
        return ShopItem::SLOT_PET;
    }

    public function equip()
    {
//        $class = new \ReflectionClass($this->m_petTypes);

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
                    new FloatTag("",  0)
                ])
            ]
        );

//        switch ($this->m_petTypes) {
//            case PetTypes::VILLAGER: {
//                $this->m_entity = new Villager($this->m_fatPlayer->getPlayer()->getLevel(), $tag);
//            }
//                break;
//            case PetTypes::ZOMBIE: {
//                $this->m_entity = new Zombie($this->m_fatPlayer->getPlayer()->getLevel(), $tag);
//            }
//                break;
//            case PetTypes::SQUID: {
//                $this->m_entity = new Squid($this->m_fatPlayer->getPlayer()->getLevel(), $tag);
//            }
//                break;
//        }
        $this->m_entity = new CustomPet($this->m_fatPlayer->getPlayer()->getLevel(), $tag, $this->m_petTypes);

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
        if($this->m_entity == null){
            echo "entity is null \n";
        }

        $dist = $this->m_entity->getLocation()->distance($this->m_fatPlayer->getPlayer()->getLocation()->asVector3());
        echo $dist."\n";
        if ($dist > 2) {
            $playerPos = $this->m_fatPlayer->getPlayer()->getLocation();
            $petPos = $this->m_entity->getLocation();
            $vec = new Vector3($playerPos->getX() - $petPos->getX(), $playerPos->getY() - $petPos->getY(), $playerPos->getZ() - $petPos->getZ());
            $vec = $vec->normalize()->multiply(0.3 * 2);
            $vecOr = new Vector3(0, 0, 1);
            $yaw = rad2deg(atan2($vec->getZ(), $vec->getX()) - atan2($vecOr->getZ(), $vecOr->getX())) % 360;
            $this->m_entity->setRotation($yaw, 0);
            $this->m_entity->setMotion($vec);
        }
    }
}