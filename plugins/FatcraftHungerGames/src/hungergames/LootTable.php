<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 16:06
 */

namespace hungergames;


use pocketmine\item\Item;

class LootTable
{
	public static $m_GeneralLoot = [
		Item::IRON_SHOVEL,
		Item::IRON_PICKAXE,
		Item::IRON_AXE,
		Item::FLINT_AND_STEEL,
		Item::APPLE,
		Item::BOW,
		Item::ARROW,
		Item::COAL,
		Item::DIAMOND,
		Item::IRON_INGOT,
		Item::GOLD_INGOT,
		Item::IRON_SWORD,
		Item::WOODEN_SWORD,
		Item::WOODEN_SHOVEL,
		Item::WOODEN_PICKAXE,
		Item::WOODEN_AXE,
		Item::STONE_SWORD,
		Item::STONE_SHOVEL,
		Item::STONE_PICKAXE,
		Item::STONE_AXE,
		Item::DIAMOND_SWORD,
		Item::DIAMOND_SHOVEL,
		Item::DIAMOND_PICKAXE,
		Item::DIAMOND_AXE,
		Item::STICK,
		Item::MUSHROOM_STEW,
		Item::GOLDEN_SWORD,
		Item::GOLDEN_SHOVEL,
		Item::GOLDEN_PICKAXE,
		Item::GOLDEN_AXE,
		Item::STRING,
		Item::FEATHER,
		Item::WOODEN_HOE,
		Item::STONE_HOE,
		Item::IRON_HOE,
		Item::DIAMOND_HOE,
		Item::GOLDEN_HOE,
		Item::BREAD,
		Item::LEATHER_HELMET,
		Item::LEATHER_CHESTPLATE,
		Item::LEATHER_LEGGINGS,
		Item::LEATHER_BOOTS,
		Item::CHAINMAIL_HELMET,
		Item::CHAINMAIL_CHESTPLATE,
		Item::CHAINMAIL_LEGGINGS,
		Item::CHAINMAIL_BOOTS,
		Item::IRON_HELMET,
		Item::IRON_CHESTPLATE,
		Item::IRON_LEGGINGS,
		Item::IRON_BOOTS,
		Item::DIAMOND_HELMET,
		Item::DIAMOND_CHESTPLATE,
		Item::DIAMOND_LEGGINGS,
		Item::DIAMOND_BOOTS,
		Item::GOLDEN_HELMET,
		Item::GOLDEN_CHESTPLATE,
		Item::GOLDEN_LEGGINGS,
		Item::GOLDEN_BOOTS,
		Item::PORKCHOP,
		Item::COOKED_PORKCHOP,
		Item::GOLDEN_APPLE,
		Item::SNOWBALL,
		Item::LEATHER,
		Item::EGG,
		Item::FISHING_ROD,
		Item::COOKED_FISH,
		Item::COOKIE,
		Item::COOKED_BEEF,
		Item::COOKED_CHICKEN,
		Item::BAKED_POTATO,
		Item::GOLDEN_CARROT,
		Item::ELYTRA,
		Item::IRON_NUGGET
	];

	public static function getHGRandomLootId()
	{
		return self::$m_GeneralLoot[rand(0, count(LootTable::$m_GeneralLoot) - 1)];
	}
}