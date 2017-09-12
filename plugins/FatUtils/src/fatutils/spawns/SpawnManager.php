<?php
/**
 * Created by PhpStorm.
 * User: Nyhven
 * Date: 07/09/2017
 * Time: 15:45
 */

namespace fatutils\spawns;

use fatutils\FatUtils;
use fatutils\tools\WorldUtils;
use pocketmine\block\Block;
use pocketmine\level\Location;

class SpawnManager
{
    private static $m_Instance = null;
    private $m_Spawns = [];

    public static function getInstance(): SpawnManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new SpawnManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        if (!is_null(FatUtils::getInstance()->getTemplateConfig()))
        {
            echo "SpawnManager loading...\n";
            foreach (FatUtils::getInstance()->getTemplateConfig()->get("spawns") as $l_RawLocation)
            {
                $this->m_Spawns[] = WorldUtils::stringToLocation($l_RawLocation);
                echo "   - " . $l_RawLocation . "\n";
            }
        }
    }

    //----------------
    // UTILS
    //----------------
    public function blockSpawns()
    {
        foreach ($this->m_Spawns as $l_Slot)
        {
            if ($l_Slot instanceof Location)
            {
                $l_SlotBlock = $l_Slot->getLevel()->getBlock($l_Slot);
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
                ], Block::GLASS);
            }
        }
    }

    public function unblockSpawns()
    {
        foreach ($this->m_Spawns as $l_Slot)
        {
            if ($l_Slot instanceof Location)
            {
                $l_SlotBlock = $l_Slot->getLevel()->getBlock($l_Slot);
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
                ], Block::AIR);
            }
        }
    }

    //----------------
    // GETTERS
    //----------------
    /**
     * @return array
     */
    public function getSpawns(): array
    {
        return $this->m_Spawns;
    }
}