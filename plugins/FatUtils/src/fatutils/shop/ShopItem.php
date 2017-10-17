<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 14:11
 */

namespace fatutils\shop;


abstract class ShopItem
{
	const FAT_PLAYER_SHOP_SLOT_PET = "pet";
	const FAT_PLAYER_SHOP_SLOT_PARTICLE = "particle";

	public abstract function getSlotName():string;

	public abstract function equip();
	public abstract function unequip();
}