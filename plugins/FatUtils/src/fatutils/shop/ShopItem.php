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
	const SLOT_PET = "pet";
	const SLOT_PARTICLE = "particle";

	private $m_Player = null;
	private $m_Key = null;
	private $m_Data = null;

	public static function createShopItem(Player $p_Player, string $p_ShopItemKey, array $p_Data): ShopItem
	{
		$l_Class = $p_Data["class"];
		return new $l_Class($p_Player, $p_ShopItemKey, $p_Data);
	}

	public function __construct(Player $p_Player, string $p_ShopItemKey, array $p_Data = [])
	{
		$this->m_Player = $p_Player;
		$this->m_Key = $p_ShopItemKey;
		$this->m_Data = $p_Data;
	}

	public function getPlayer(): Player
	{
		return $this->m_Player;
	}

	public function getKey(): string
	{
		return $this->m_Key;
	}

	public function getData(): array
	{
		return $this->m_Data;
	}

	public function getDataValue(string $p_Key, $p_Default = null)
	{
		if (array_key_exists($p_Key, $this->m_Data))
			return $this->m_Data[$p_Key];
		else
			return $p_Default;
	}

	public function getName(): ?string
	{
		return $this->getDataValue("name");
	}

	public function getDescription(): ?string
	{
		return $this->getDataValue("desc");
	}

	public function getImage(): ?string
	{
		return $this->getDataValue("img", "");
	}

	public function getFatcoinPrice(): int
	{
		return $this->getDataValue("priceFC", -1);
	}

	public function getFatbillPrice(): int
	{
		return $this->getDataValue("priceFB", -1);
	}

	public abstract function getSlotName(): string;

	public abstract function equip();

	public abstract function unequip();
}