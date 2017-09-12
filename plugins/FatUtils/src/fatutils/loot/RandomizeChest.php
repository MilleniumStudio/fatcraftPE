<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 11/09/2017
 * Time: 14:23
 */

namespace fatutils\loot;


use fatutils\tools\WorldUtils;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\tile\Chest;

class RandomizeChest
{
    private $m_Position = null;
    private $m_TotalItemValue = 100;
    private $m_MinItemValue = -1;
    private $m_MaxItemValue = -1;

    /**
     * RandomizeChest constructor.
     * @param $position
     */
    public function __construct(Position $position)
    {
        $this->m_Position = $position;
        $l_ChestBlock = $this->m_Position->getLevel()->getBlock($this->m_Position);

        WorldUtils::loadChunkAt($this->m_Position);
        if (!($l_ChestBlock->getId() == BlockIds::CHEST ||
            $l_ChestBlock->getId() == BlockIds::TRAPPED_CHEST))
        {
            echo "No chest found on " . $this->m_Position . ", placing one...\n";
            $this->m_Position->getLevel()->setBlock($this->m_Position, BlockFactory::get(BlockIds::CHEST));
        }
    }

    //--------
    // UTILS
    //--------
    public function fillChest()
    {
        if ($this->m_Position instanceof Position)
        {
            echo "Filling chest at " . $this->getPosition() . "\n";
            WorldUtils::loadChunkAt($this->m_Position);
            $l_ChestBlock = $this->m_Position->getLevel()->getBlock($this->m_Position);
            $l_ChestTile = $this->m_Position->getLevel()->getTile($l_ChestBlock);
            if ($l_ChestTile instanceof Chest)
            {
                $l_ChestTile->getInventory()->clearAll();

                $l_InventoryTotalValue = 0;
                while ($l_InventoryTotalValue < $this->m_TotalItemValue)
                {
                    $l_LootTable = LootManager::getInstance()->getRandomLootTable($this->m_MinItemValue, $this->m_MaxItemValue);
                    $l_InventoryTotalValue += $l_LootTable->getItemValue();
                    $l_Item = $l_LootTable->getRandomItem();
                    if ($l_ChestTile->getInventory()->firstEmpty() != -1)
                    {
                        $l_EmptySlot = null;
                        while (is_null($l_EmptySlot))
                        {
                            $l_slot = rand(0, $l_ChestTile->getInventory()->getSize() - 1);
                            $item = $l_ChestTile->getInventory()->getItem($l_slot);
                            if (!is_null($item) && $item->getId() == ItemIds::AIR)
                                $l_EmptySlot = $l_slot;
                        }
                        echo "   - item[" . $l_EmptySlot . "]= " . $l_Item . "\n";
                        $l_ChestTile->getInventory()->setItem($l_EmptySlot, $l_Item);
                    }
                }
            }
        }
    }

    //--------
    // SETTERS
    //--------
    /**
     * @param int $p_MinItemValue
     */
    public function setMinItemValue(int $p_MinItemValue)
    {
        $this->m_MinItemValue = $p_MinItemValue;
    }

    /**
     * @param int $p_MaxItemValue
     */
    public function setMaxItemValue(int $p_MaxItemValue)
    {
        $this->m_MaxItemValue = $p_MaxItemValue;
    }

    /**
     * @param int $p_TotalItemValue
     */
    public function setTotalItemValue(int $p_TotalItemValue)
    {
        $this->m_TotalItemValue = $p_TotalItemValue;
    }

    /**
     * @return null|Position
     */
    public function getPosition()
    {
        return $this->m_Position;
    }

    public function __toString()
    {
        return $this->m_Position . " (totalValue: " . $this->m_TotalItemValue . ", minValue: " . $this->m_MinItemValue . ", maxValue: " . $this->m_MaxItemValue . ")";
    }
}