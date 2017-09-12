<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 11/09/2017
 * Time: 11:33
 */

namespace fatutils\loot;


use fatutils\tools\ItemUtils;
use pocketmine\item\Item;

class LootTable
{
    private $m_ItemValue = 0;
    private $m_Chance = 0;
    private $m_items = [];

    /**
     * LootConfig constructor.
     * @param array $p_LootTableConfig
     */
    public function __construct(array $p_LootTableConfig)
    {
        if (array_key_exists(LootManager::CONFIG_KEY_LOOT_ITEM_VALUE, $p_LootTableConfig))
            $this->m_ItemValue = $p_LootTableConfig[LootManager::CONFIG_KEY_LOOT_ITEM_VALUE];

        if (array_key_exists(LootManager::CONFIG_KEY_LOOT_CHANCE, $p_LootTableConfig))
            $this->m_Chance = $p_LootTableConfig[LootManager::CONFIG_KEY_LOOT_CHANCE];

        if (array_key_exists(LootManager::CONFIG_KEY_LOOT_ITEMS, $p_LootTableConfig) && gettype($p_LootTableConfig[LootManager::CONFIG_KEY_LOOT_ITEMS]) == 'array')
        {
            foreach ($p_LootTableConfig[LootManager::CONFIG_KEY_LOOT_ITEMS] as $l_ItemConfig)
            {
                $l_Item = ItemUtils::getItemFromRaw($l_ItemConfig);
                if (!is_null($l_Item))
                    $this->m_items[] = $l_Item;
            }
        }
    }

    public function getRandomItem():Item
    {
        return $this->m_items[rand(0, count($this->m_items) - 1)];
    }

    /**
     * @return int|mixed
     */
    public function getItemValue():int
    {
        return $this->m_ItemValue;
    }

    /**
     * @return int|mixed
     */
    public function getChance():int
    {
        return $this->m_Chance;
    }

    public function __toString()
    {
        return "LootTable{" .
                    "itemValue:" . $this->m_ItemValue . ", " .
                    "chance:" . $this->m_Chance. ", " .
                    "items:" . $this->m_items . "}";
    }


}