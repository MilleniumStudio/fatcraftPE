<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 14:11
 */

namespace fatutils\shop\particles;

use fatutils\shop\ShopItem;

class ParticleItem extends ShopItem
{
	public function getSlotName(): string
	{
		return ShopItem::FAT_PLAYER_SHOP_SLOT_PARTICLE;
	}

	public function equip()
	{
		var_dump($this->getPlayer()->getName() . " equipped", $this->getData());
	}

	public function unequip()
	{
		// TODO: Implement unequip() method.
	}
}