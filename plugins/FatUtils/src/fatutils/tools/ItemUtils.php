<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 16:03
 */

namespace fatutils\tools;
use fatutils\FatUtils;
use pocketmine\block\BlockIds;
use pocketmine\item\ItemFactory;
use ReflectionClass;

class ItemUtils
{
    private static $m_ItemsName = null;

    const RAW_ITEM_MATERIAL = "material";
    const RAW_ITEM_DATA = "data";
    const RAW_ITEM_AMOUNT = "amount";

    public static function getItemIdFromName(string $p_RawMaterial)
    {
        if (is_null(ItemUtils::$m_ItemsName))
        {
            $class = new ReflectionClass("pocketmine\item\ItemIds");
            ItemUtils::$m_ItemsName = $class->getConstants();
        }

        if (array_key_exists($p_RawMaterial, ItemUtils::$m_ItemsName))
            return ItemUtils::$m_ItemsName[$p_RawMaterial];

        FatUtils::getInstance()->getLogger()->error("Raw Material does not exist: " . $p_RawMaterial);
        return BlockIds::STONE;
    }

    public static function getItemFromRaw(string $p_RawItem)
    {
        $l_Ret = null;

        $l_Json = json_decode($p_RawItem, true);
        if (!is_null($l_Json) && gettype($l_Json) == 'array')
        {
            $l_Ret = ItemFactory::get(
                self::getItemIdFromName(array_key_exists(ItemUtils::RAW_ITEM_MATERIAL, $l_Json) ? (string)$l_Json[ItemUtils::RAW_ITEM_MATERIAL] : "STONE"),
                array_key_exists(ItemUtils::RAW_ITEM_DATA, $l_Json) ? (int)$l_Json[ItemUtils::RAW_ITEM_DATA] : 0,
                array_key_exists(ItemUtils::RAW_ITEM_AMOUNT, $l_Json) ? (int)$l_Json[ItemUtils::RAW_ITEM_AMOUNT] : 1
            );
        } else
            FatUtils::getInstance()->getServer()->getLogger()->error("Incorrect raw item: " . $p_RawItem);

        return $l_Ret;
    }
}