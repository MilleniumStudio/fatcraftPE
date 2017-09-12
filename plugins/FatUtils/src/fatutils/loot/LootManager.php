<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 11/09/2017
 * Time: 11:07
 */

namespace fatutils\loot;


use fatutils\FatUtils;
use fatutils\tools\WeightedRandom;
use pocketmine\item\Item;

class LootManager
{
    const CONFIG_KEY_LOOT_ROOT = "loots";
    const CONFIG_KEY_LOOT_ITEMS = "items";
    const CONFIG_KEY_LOOT_CHANCE = "chance";
    const CONFIG_KEY_LOOT_ITEM_VALUE = "item-value";

    private static $m_Instance = null;

    private $m_LootTables = [];
    private $m_MainWeightedRandom;

    public static function getInstance(): LootManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new LootManager();
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
            $l_FullConfig = FatUtils::getInstance()->getTemplateConfig()->get(LootManager::CONFIG_KEY_LOOT_ROOT);

            foreach ($l_FullConfig as $l_LootKey)
            {
                if (gettype($l_LootKey) == "array")
                {
                    $this->m_LootTables[] = new LootTable($l_LootKey);
                }
            }

            $weights = [];
            foreach ($this->m_LootTables as $lootTable)
            {
                if ($lootTable instanceof LootTable)
                    $weights[] = $lootTable->getChance();
            }
            $this->m_MainWeightedRandom = new WeightedRandom($weights);
        }
    }

    public function getRandomLootTable(int $p_MinItemValue = -1, int $p_MaxItemValue = -1):LootTable
    {
        $l_WRandom = $this->m_MainWeightedRandom;
        if ($p_MinItemValue != -1 || $p_MaxItemValue != -1)
        {
            $l_CustomWeights = [];
            foreach ($this->m_LootTables as $l_LootTable)
            {
                if ($l_LootTable instanceof LootTable && ($l_LootTable->getItemValue() >= $p_MinItemValue && $l_LootTable->getItemValue() <= $p_MaxItemValue))
                    $l_CustomWeights[] = $l_LootTable->getChance();
                else
                    $l_CustomWeights[] = 0;
            }
            $l_WRandom = new WeightedRandom($l_CustomWeights);
        }

        if ($l_WRandom instanceof WeightedRandom)
        {
            $index = $l_WRandom->getRandomIndex();
            $l_LootTable = $this->m_LootTables[$index];

            if ($l_LootTable instanceof LootTable)
                return $l_LootTable;
        }

        return null;
    }

    public function getRandomItem(int $p_MinItemValue = -1, int $p_MaxItemValue = -1):Item
    {
        return $this->getRandomLootTable($p_MinItemValue, $p_MaxItemValue)->getRandomItem();
    }
}