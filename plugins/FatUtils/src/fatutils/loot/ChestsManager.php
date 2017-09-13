<?php
/**
 * Created by PhpStorm.
 * User: Nyhven
 * Date: 07/09/2017
 * Time: 15:45
 */

namespace fatutils\loot;

use fatutils\FatUtils;
use fatutils\tools\WorldUtils;

class ChestsManager
{
    const CONFIG_KEY_CHEST_ROOT = "chests";
    const CONFIG_KEY_CHEST_LOCATION = "location";
    const CONFIG_KEY_CHEST_TOTAL_VALUE = "total-item-value";
    const CONFIG_KEY_CHEST_MIN_ITEM_VALUE = "min-item-value";
    const CONFIG_KEY_CHEST_MAX_ITEM_VALUE = "max-item-value";

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
        foreach (FatUtils::getInstance()->getTemplateConfig()->get(ChestsManager::CONFIG_KEY_CHEST_ROOT) as $l_RandomizeChestConf)
        {
            echo "Chests loading...\n";
            $l_Loc = WorldUtils::stringToLocation($l_RandomizeChestConf[ChestsManager::CONFIG_KEY_CHEST_LOCATION]);
            $l_RandomizeChest = new RandomizeChest($l_Loc);

            if (array_key_exists(ChestsManager::CONFIG_KEY_CHEST_TOTAL_VALUE, $l_RandomizeChestConf))
                $l_RandomizeChest->setTotalItemValue($l_RandomizeChestConf[ChestsManager::CONFIG_KEY_CHEST_TOTAL_VALUE]);

            if (array_key_exists(ChestsManager::CONFIG_KEY_CHEST_MIN_ITEM_VALUE, $l_RandomizeChestConf))
                $l_RandomizeChest->setMinItemValue($l_RandomizeChestConf[ChestsManager::CONFIG_KEY_CHEST_MIN_ITEM_VALUE]);

            if (array_key_exists(ChestsManager::CONFIG_KEY_CHEST_MAX_ITEM_VALUE, $l_RandomizeChestConf))
                $l_RandomizeChest->setMaxItemValue($l_RandomizeChestConf[ChestsManager::CONFIG_KEY_CHEST_MAX_ITEM_VALUE]);

            $this->m_Chests[] = $l_RandomizeChest;
            echo "   - " . $l_RandomizeChest . "\n";
        }
    }

    //----------------
    // UTILS
    //----------------
    public function fillChests()
    {
        foreach ($this->getChests() as $l_ChestLocation)
        {
            if ($l_ChestLocation instanceof RandomizeChest)
                $l_ChestLocation->fillChest();
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