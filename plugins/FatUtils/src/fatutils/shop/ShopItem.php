<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 14:11
 */

namespace fatutils\shop;


use pocketmine\Player;

abstract class ShopItem
{
	const FAT_PLAYER_SHOP_SLOT_PET = "pet";
	const FAT_PLAYER_SHOP_SLOT_PARTICLE = "particle";

	private $m_Player = null;
	private $m_Data = null;

	public static function instanciateShopitem(Player $p_Player, array $p_Data):ShopItem
	{
		$l_Class = $p_Data["class"];
		return new $l_Class($p_Player, $p_Data);
	}

	public function __construct(Player $p_Player, array $p_Data = [])
	{
		$this->m_Player = $p_Player;
		$this->m_Data = $p_Data;
	}

	public function getPlayer():Player
	{
		return $this->m_Player;
	}

	public function getData():array
	{
		return $this->m_Data;
	}

	public function getName():string
	{
		return $this->getData()["name"];
	}

	public abstract function getSlotName():string;

	public abstract function equip();
	public abstract function unequip();
}