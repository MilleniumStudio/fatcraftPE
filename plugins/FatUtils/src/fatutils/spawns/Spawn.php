<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 18:37
 */

namespace fatutils\spawns;


use fatutils\FatUtils;
use fatutils\tools\WorldUtils;
use pocketmine\block\BlockIds;
use pocketmine\level\Location;
use pocketmine\Player;

class Spawn
{
    private $m_Name;

    private $m_Location;
    private $m_BlockType = BlockIds::AIR;
    private $m_BarrierType = BlockIds::GLASS;

    /**
     * Spawn constructor.
     * @param $m_Location
     */
    public function __construct($m_Location)
    {
        $this->m_Location = $m_Location;
    }

    /**
     * @param int $m_BlockType
     */
    public function setBlockType(int $m_BlockType)
    {
        $this->m_BlockType = $m_BlockType;
    }

    /**
     * If the block type at m_Location is not equal to m_BlockType,
     *  spawn is not longer active.
     */
    public function isActive()
    {
        if ($this->m_Location instanceof Location)
        {
            WorldUtils::loadChunkAt($this->m_Location);

            //debug
//            $l_IdAt = $this->m_Location->getLevel()->getBlockIdAt($this->m_Location->getX(), $this->m_Location->getY(), $this->m_Location->getZ());
//            FatUtils::getInstance()->getLogger()->info("IdAt " . $this->m_Location . "=" . $l_IdAt);
//            if ($l_IdAt != BlockIds::BED_BLOCK)
//                $this->m_Location->getLevel()->setBlockIdAt($this->m_Location->getX(), $this->m_Location->getY(), $this->m_Location->getZ(), BlockIds::GLASS);

            return $this->m_BlockType === $this->m_Location->getLevel()->getBlockIdAt($this->m_Location->getX(), $this->m_Location->getY(), $this->m_Location->getZ());
        }

        return false;
    }

    /**
     * @param int $m_BarrierType
     */
    public function setBarrierType(int $m_BarrierType)
    {
        $this->m_BarrierType = $m_BarrierType;
    }

    public function isEmpty(): bool
    {
        if ($this->m_Location instanceof Location)
        {
            $l_NearbyEntities = $this->m_Location->getLevel()
                ->getNearbyEntities(WorldUtils::getRadiusBB($this->m_Location, doubleval(1)));

            if (count($l_NearbyEntities) == 0)
                return true;
            else
                return false;
        }

        return false;
    }

    public function blockSpawn()
    {
        if ($this->m_Location instanceof Location)
        {
            WorldUtils::loadChunkAt($this->m_Location);
            $l_SlotBlock = $this->m_Location->getLevel()->getBlock($this->m_Location);
            WorldUtils::setBlocksId([
                WorldUtils::getRelativeBlock($l_SlotBlock, -1, 0, 0),
                WorldUtils::getRelativeBlock($l_SlotBlock, 1, 0, 0),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 0, -1),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 0, 1),
                WorldUtils::getRelativeBlock($l_SlotBlock, -1, 1, 0),
                WorldUtils::getRelativeBlock($l_SlotBlock, 1, 1, 0),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 1, -1),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 1, 1),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 2, 0)
            ], $this->m_BarrierType);
        }
    }

    public function unblockSpawn()
    {
        if ($this->m_Location instanceof Location)
        {
            $l_SlotBlock = $this->m_Location->getLevel()->getBlock($this->m_Location);
            WorldUtils::setBlocksId([
                WorldUtils::getRelativeBlock($l_SlotBlock, -1, 0, 0),
                WorldUtils::getRelativeBlock($l_SlotBlock, 1, 0, 0),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 0, -1),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 0, 1),
                WorldUtils::getRelativeBlock($l_SlotBlock, -1, 1, 0),
                WorldUtils::getRelativeBlock($l_SlotBlock, 1, 1, 0),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 1, -1),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 1, 1),
                WorldUtils::getRelativeBlock($l_SlotBlock, 0, 2, 0)
            ], BlockIds::AIR);
        }
    }

    /**
     * @param Player $p_Player
     * @param float $p_HRandomize horizontal randomization
     */
    public function teleport(Player $p_Player, float $p_HRandomize = 0)
    {
        if ($p_HRandomize === 0)
            $p_Player->teleport($this->getLocation());
        else
            $p_Player->teleport(WorldUtils::getRandomizedLocation($this->getLocation(), $p_HRandomize, 0, $p_HRandomize));
    }

    public function getLocation(): Location
    {
        return $this->m_Location;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->m_Name;
    }

    /**
     * @param mixed $m_Name
     */
    public function setName($m_Name)
    {
        $this->m_Name = $m_Name;
    }
}