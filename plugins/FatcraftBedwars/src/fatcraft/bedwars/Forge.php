<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 15/09/2017
 * Time: 17:17
 */

namespace fatcraft\bedwars;


use fatutils\FatUtils;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;

class Forge
{
    private $m_Location;

    private $m_ItemType = ItemIds::IRON_INGOT;
    private $m_PopDelay = 100;
    private $m_PopAmount = 10;

    private $m_LastTickPop;

    /**
     * Forge constructor.
     * @param $p_Location
     */
    public function __construct($p_Location)
    {
        $this->m_Location = $p_Location;
        $this->m_LastTickPop = FatUtils::getInstance()->getServer()->getTick() - $this->m_PopDelay;
    }

    /**
     * @param mixed $m_ItemType
     */
    public function setItemType($m_ItemType)
    {
        $this->m_ItemType = $m_ItemType;
    }

    /**
     * @param mixed $m_PopDelay
     */
    public function setPopDelay($m_PopDelay)
    {
        $this->m_PopDelay = $m_PopDelay;
    }

    /**
     * @param mixed $m_AmountOnPop
     */
    public function setPopAmount($m_AmountOnPop)
    {
        $this->m_PopAmount = $m_AmountOnPop;
    }

    /**
     * @return int
     */
    public function getPopDelay(): int
    {
        return $this->m_PopDelay;
    }

    /**
     * @return int
     */
    public function getPopAmount(): int
    {
        return $this->m_PopAmount;
    }

    public function canPop():bool
    {
        return (bool)FatUtils::getInstance()->getServer()->getTick() - $this->m_PopDelay > $this->m_LastTickPop;
    }

    public function pop()
    {
        if ($this->m_Location instanceof Location)
        {
            FatUtils::getInstance()->getLogger()->info("POP of " . $this->m_ItemType . "x" . $this->m_ItemType);
            $this->m_Location->getLevel()->dropItem($this->m_Location, Item::get($this->m_ItemType));
            $this->m_LastTickPop = FatUtils::getInstance()->getServer()->getTick();
        }
    }
}