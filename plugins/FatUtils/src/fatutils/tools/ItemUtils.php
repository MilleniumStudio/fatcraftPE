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
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use ReflectionClass;

class ItemUtils
{
    private static $m_ItemsName = null;

    const RAW_ITEM_MATERIAL = "material";
    const RAW_ITEM_DATA = "data";
    const RAW_ITEM_AMOUNT = "amount";

    public static function getItemIdFromName(string $p_RawMaterial)
    {
        if (is_null(self::$m_ItemsName))
        {
            $class = new ReflectionClass("pocketmine\item\ItemIds");
            self::$m_ItemsName = $class->getConstants();
        }

        if (array_key_exists($p_RawMaterial, self::$m_ItemsName))
            return self::$m_ItemsName[$p_RawMaterial];

        FatUtils::getInstance()->getLogger()->error("Raw Material does not exist: " . $p_RawMaterial);
        return BlockIds::STONE;
    }

    /**
     * @param string $p_RawItem as '{"material": "COBBLESTONE", "data": 0, "amount": 10}'
     * @return null|Item
     */
    public static function getItemFromRaw(string $p_RawItem): ?Item
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

    /**
     * @param Item $p_Item
     * @param string $p_Color {@link fatutils/tools/ColorUtils}
     * @return Item
     */
    public static function getColoredItemIfColorable(Item $p_Item, string $p_Color)
    {
        switch ($p_Item->getId())
        {
            case BlockIds::WOOL:
            case BlockIds::STAINED_CLAY:
            case BlockIds::STAINED_GLASS:
            case BlockIds::STAINED_GLASS_PANE:
            case BlockIds::SHULKER_BOX:
                $p_Item->setDamage(ColorUtils::getMetaFromColor($p_Color));
                break;
            case ItemIds::LEATHER_HELMET:
            case ItemIds::LEATHER_CAP:
            case ItemIds::LEATHER_CHESTPLATE:
            case ItemIds::LEATHER_LEGGINGS:
            case ItemIds::LEATHER_BOOTS:
                if(($hasTag = $p_Item->hasCompoundTag())){
                    $tag = $p_Item->getNamedTag();
                }else{
                    $tag = new CompoundTag("", []);
                }
                $tag->customColor = new IntTag("customColor", ColorUtils::getColorCode(ColorUtils::getColorFromColor($p_Color)));
                $p_Item->setCompoundTag($tag);
                break;
        }

        return $p_Item;
    }

    public static function isArmor(int $p_Id):bool
    {
        return self::isHelmet($p_Id) || self::isChestplate($p_Id) || self::isLeggings($p_Id) || self::isBoots($p_Id);
    }

    public static function isHelmet(int $p_Id):bool
    {
        switch ($p_Id)
        {
            case ItemIds::LEATHER_HELMET:
            case ItemIds::CHAIN_HELMET:
            case ItemIds::CHAINMAIL_HELMET:
            case ItemIds::DIAMOND_HELMET:
            case ItemIds::GOLD_HELMET:
            case ItemIds::GOLDEN_HELMET:
            case ItemIds::IRON_HELMET:
            case ItemIds::MOB_HEAD:
            case BlockIds::PUMPKIN:
                return true;
                break;
        }

        return false;
    }

    public static function isChestplate(int $p_Id):bool
    {
        switch ($p_Id)
        {
            case ItemIds::LEATHER_CHESTPLATE:
            case ItemIds::CHAIN_CHESTPLATE:
            case ItemIds::CHAINMAIL_CHESTPLATE:
            case ItemIds::DIAMOND_CHESTPLATE:
            case ItemIds::GOLD_CHESTPLATE:
            case ItemIds::GOLDEN_CHESTPLATE:
            case ItemIds::IRON_CHESTPLATE:
                return true;
                break;
        }

        return false;
    }

    public static function isLeggings(int $p_Id):bool
    {
        switch ($p_Id)
        {
            case ItemIds::LEATHER_LEGGINGS:
            case ItemIds::CHAIN_LEGGINGS:
            case ItemIds::CHAINMAIL_LEGGINGS:
            case ItemIds::DIAMOND_LEGGINGS:
            case ItemIds::GOLD_LEGGINGS:
            case ItemIds::GOLDEN_LEGGINGS:
            case ItemIds::IRON_LEGGINGS:
                return true;
                break;
        }

        return false;
    }

    public static function isBoots(int $p_Id):bool
    {
        switch ($p_Id)
        {
            case ItemIds::LEATHER_BOOTS:
            case ItemIds::CHAIN_BOOTS:
            case ItemIds::CHAINMAIL_BOOTS:
            case ItemIds::DIAMOND_BOOTS:
            case ItemIds::GOLD_BOOTS:
            case ItemIds::GOLDEN_BOOTS:
            case ItemIds::IRON_BOOTS:
                return true;
                break;
        }

        return false;
    }
}