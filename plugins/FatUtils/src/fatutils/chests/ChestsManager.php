<?php
/**
 * Created by PhpStorm.
 * User: Nyhven
 * Date: 07/09/2017
 * Time: 15:45
 */

namespace fatutils\chests;

use fatutils\FatUtils;
use fatutils\tools\WorldUtils;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\tile\Chest;

class ChestsManager
{
    private static $m_Instance = null;
    private $m_Chests = [];

    public static function getInstance(): ChestsManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new ChestsManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {

        echo "Chests loading...\n";
        foreach (FatUtils::getInstance()->getTemplateConfig()->get("chests") as $l_RawLocation)
        {
            $l_Loc = WorldUtils::stringToLocation($l_RawLocation);
            $this->m_Chests[] = $l_Loc;
            echo "Chests loaded at " . $l_Loc . "\n";
        }
    }

    //----------------
    // UTILS
    //----------------
    public function fillChests(array $p_LootPossibilities)
    {
        foreach ($this->getChests() as $l_ChestLocation)
        {
            if ($l_ChestLocation instanceof Location)
            {
                WorldUtils::loadChunkAt($l_ChestLocation);
                $l_ChestBlock = $l_ChestLocation->getLevel()->getBlock($l_ChestLocation);
                if ($l_ChestBlock->getId() == Block::CHEST || $l_ChestBlock->getId() == Block::TRAPPED_CHEST)
                {
                    $l_ChestTile = $l_ChestLocation->getLevel()->getTile($l_ChestBlock);
                    echo "ChestAT: " . WorldUtils::locationToString($l_ChestLocation) . " " . $l_ChestBlock->getId() . " tile=" . $l_ChestTile . "\n";
                    if ($l_ChestTile instanceof Chest)
                    {
                        $l_ChestTile->getInventory()->clearAll();
                        for ($i = 0, $l = rand(2, 10); $i <= $l; $i++)
                        {
                            $slot = rand(0, $l_ChestTile->getInventory()->getSize() - 1);
                            $item = new Item($p_LootPossibilities[rand(0, count($p_LootPossibilities) - 1)]);
                            echo "item[" . $slot . "]= " . $item . "\n";
                            $l_ChestTile->getInventory()->setItem($slot, $item);
                        }
                    }
                } else
                    echo "NoChestAT: " . WorldUtils::locationToString($l_ChestLocation) . "... blockId=" . $l_ChestBlock->getId() . "\n";
            }
        }
    }

    //----------------
    // GETTERS
    //----------------
    /**
     * @return array
     */
    public function getChests(): array
    {
        return $this->m_Chests;
    }
}