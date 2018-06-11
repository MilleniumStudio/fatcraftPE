<?php

namespace fatutils\shop\paintball;

use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\shop\ShopItem;

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