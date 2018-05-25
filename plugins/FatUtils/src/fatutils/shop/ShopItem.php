<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 14:11
 */

namespace fatutils\shop;


use fatutils\tools\ArrayUtils;
use pocketmine\entity\Entity;
use pocketmine\Player;

abstract class ShopItem
{
	const SLOT_PET = "pet";
	const SLOT_PARTICLE = "particle";
	const SLOT_PAINTBALL = "paintball";

	private $m_Entity = null;
	private $m_Key = null;
	private $m_Data = null;

	public static function createShopItem(Entity $p_Entity, string $p_ShopItemKey, array $p_Data): ShopItem
	{
		$l_Class = $p_Data["class"];
		return new $l_Class($p_Entity, $p_ShopItemKey, $p_Data);
	}

	public function __construct(Entity $p_Entity, string $p_ShopItemKey, array $p_Data = [])
	{
		$this->m_Entity = $p_Entity;
		$this->m_Key = $p_ShopItemKey;
		$this->m_Data = $p_Data;
	}

	public function getEntity(): Entity
	{
		return $this->m_Entity;
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
		return ArrayUtils::getKeyOrDefault($this->m_Data, $p_Key, $p_Default);
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

	public function getFatsilverPrice(): int
	{
		return $this->getDataValue("priceFS", -1);
	}

	public function getFatgoldPrice(): int
	{
		return $this->getDataValue("priceFG", -1);
	}

	public function getRankAccess(): int
    {
        return $this->getDataValue("rankVIP", -1);
    }

	public abstract function getSlotName(): string;

	public abstract function equip();

	public abstract function unequip();
}