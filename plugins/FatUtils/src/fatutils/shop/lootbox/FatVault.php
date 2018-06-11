<?php

namespace fatutils\shop\lootbox;

use fatutils\players\FatPlayer;
use fatutils\shop\ShopItem;

class FatVault extends ShopItem
{
    /** @var  FatPlayer $m_fatPlayer */
    private $m_fatPlayer;
    private $m_paintName;
    private $m_time;


    public function getSlotName(): string
    {
        return "";
    }

    public function equip()
    {

    }

    public function unequip()
    {

    }

    public function updatePosition()
    {
    }
}