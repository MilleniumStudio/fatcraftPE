<?php

namespace fatutils\pets;

use fatutils\players\FatPlayer;
use fatutils\shop\ShopItem;
use pocketmine\entity\Entity;
use pocketmine\entity\Villager;
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
        return ShopItem::FAT_PLAYER_SHOP_SLOT_PET;
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
                    new FloatTag("", 0)
                ])
            ]
        );

        switch ($this->m_petTypes) {
            case PetTypes::VILLAGER: {
                $this->m_entity = new Villager($this->m_fatPlayer->getPlayer()->getLevel(), $tag);
            }
                break;
            case PetTypes::ZOMBIE: {

            }
                break;
            case PetTypes::SQUID: {

            }
                break;
        }
//        $this->m_entity = Entity::createEntity($this->m_petTypes, $this->m_fatPlayer->getPlayer()->getLocation()->getLevel(), $tag);
//        $this->m_entity = $class->getConstructor()->invoke($this->m_fatPlayer->getPlayer()->getLevel(), $tag);
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
        $dist = $this->m_entity->getLocation()->distance($this->m_fatPlayer->getPlayer()->getLocation()->asVector3());
        if ($dist > 2) {
            $playerPos = $this->m_fatPlayer->getPlayer()->getLocation();
            $petPos = $this->m_entity->getLocation();
            $vec = new Vector3($playerPos->getX()-$petPos->getX(), $playerPos->getY()-$petPos->getY(), $playerPos->getZ()-$petPos->getZ());
            $vec = $vec->normalize()->multiply(0.2*5);

            $this->m_entity->setMotion($vec);
            //todo handle yaw !

            $this->m_entity->spawnToAll();
        }
    }
}