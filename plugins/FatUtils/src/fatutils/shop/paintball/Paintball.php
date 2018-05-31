<?php

namespace fatutils\shop\paintball;

use fatcraft\loadbalancer\LoadBalancer;
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
class Paintball extends ShopItem
{
    /** @var  FatPlayer $m_fatPlayer */
    private $m_fatPlayer;
    private $m_paintName;
    private $m_time;


    public function getSlotName(): string
    {
        return ShopItem::SLOT_PAINTBALL;
    }

    public function equip()
    {
        $this->m_fatPlayer = PlayersManager::getInstance()->getFatPlayer($this->getEntity());

        //$this->m_time = $this->getDataValue("time", array_key_exists("time", PetTypes::ENTITIES[$this->m_paintName])?PetTypes::ENTITIES[$this->m_paintName]["time"]:[]);
        $this->m_fatPlayer->setSlot(ShopItem::SLOT_PAINTBALL, $this);
        var_dump($this->m_fatPlayer->getSlots());
    }

    public function unequip()
    {
        $this->m_fatPlayer = PlayersManager::getInstance()->getFatPlayer($this->getEntity());

        if ($this->m_fatPlayer->getSlot(ShopItem::SLOT_PAINTBALL) != null)
            $this->m_fatPlayer->emptySlot(ShopItem::SLOT_PAINTBALL);
    }

    public function updatePosition()
    {
    }
}