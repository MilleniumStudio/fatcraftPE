<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 15/09/2017
 * Time: 17:17
 */

namespace fatcraft\bedwars;


use fatutils\FatUtils;
use fatutils\teams\Team;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;

class Forge
{
    private $m_ItemType = ItemIds::IRON_INGOT;
    private $m_team = null;
    private $m_Location;
    private $m_level = 0;
    private $m_PopDelay = [];
    private $m_LastTickPop;


    /**
     * Forge constructor.
     * @param $p_Location
     */
    public function __construct($p_Location)
    {
        $this->m_Location = $p_Location;
        $this->m_LastTickPop = FatUtils::getInstance()->getServer()->getTick();
    }

    /**
     * @param mixed $m_ItemType
     */
    public function setItemType($m_ItemType)
    {
        $this->m_ItemType = $m_ItemType;
    }
    public function getItemType(){
        return $this->m_ItemType;
    }

    /**
     * @param mixed $m_PopDelay
     */
    public function setPopDelay($level, $m_PopDelay)
    {
        $this->m_PopDelay[$level] = $m_PopDelay;
    }

    public function setTeam($p_team)
    {
        $this->m_team = $p_team;
    }

    public function getTeam(): ?String
    {
        return $this->m_team;
    }

    /**
     * @return int
     */
    public function getPopDelay(): int
    {
        return $this->m_PopDelay[$this->m_level];
    }

    public function getPopDelays(){
        return $this->m_PopDelay;
    }

    public function canPop(): bool
    {
        return FatUtils::getInstance()->getServer()->getTick() - $this->m_PopDelay[$this->m_level] > $this->m_LastTickPop;
    }

    public function pop()
    {
        if ($this->m_Location instanceof Location) {
//            FatUtils::getInstance()->getLogger()->info("POP of " . $this->m_ItemType . "x" . $this->m_ItemType);
            $this->m_Location->getLevel()->dropItem($this->m_Location, Item::get($this->m_ItemType));
            $this->m_LastTickPop = FatUtils::getInstance()->getServer()->getTick();
        }
    }

    public function upgrade(){
        if(count($this->m_PopDelay)>$this->m_level) {
            $this->m_level++;
            return true;
        }
        else{
            return false;
        }
    }

    public function getLevel():int{
        return $this->m_level;
    }
}