<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 14:11
 */

namespace fatutils\shop;

use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\ui\windows\Window;
use pocketmine\Player;
use pocketmine\utils\Config;

class ShopManager
{
	private $m_ShopItems = [];
	private static $m_Instance = null;

	public static function getInstance()
	{
		if (self::$m_Instance == null)
			self::$m_Instance = new ShopManager();
		return self::$m_Instance;
	}

	private function __construct()
	{
		$this->init();
	}

	private function init()
	{
		$l_Config = new Config(FatUtils::getInstance()->getDataFolder() . "shop.yml");

		if ($l_Config->exists("content"))
			$this->m_ShopItems = $l_Config->getNested("content");
	}

	public function getShopContent(): array
	{
		return $this->m_ShopItems;
	}

	public function getShopMenu(Player $p_Player): Window
	{
		$l_Ret = new ButtonWindow($p_Player);
		$l_Ret->setTitle("Shop");

		$l_ShopContent = $this->getShopContent();
		if (array_key_exists("particles", $l_ShopContent))
		{
			$l_Ret->addPart((new Button())
				->setText("shop.particles")
				->setCallback(function () use ($p_Player) {
					$this->getGenericCategory("particles", $p_Player)->open();
				})
			);
		}
		if (array_key_exists("pets", $l_ShopContent))
		{
			$l_Ret->addPart((new Button())
				->setText("shop.pets")
				->setCallback(function () use ($p_Player) {
					$this->getGenericCategory("pets", $p_Player)->open();
				})
			);
		}

		return $l_Ret;
	}

	public function getGenericCategory(string $p_CategoryName, Player $p_Player):Window
	{
		$l_Ret = new ButtonWindow($p_Player);
		$l_Ret->setTitle($p_CategoryName);

		foreach ($this->getShopContent()[$p_CategoryName] as $l_Key => $l_Pet)
		{
			$l_ShopItem = ShopItem::instanciateShopitem($p_Player, $this->getShopContent()[$p_CategoryName][$l_Key]);
			$l_Ret->addPart((new Button())
				->setText((new TextFormatter($l_ShopItem->getName()))->asStringForPlayer($p_Player))
				->setCallback(function () use ($l_ShopItem) {
					$l_ShopItem->equip();
					PlayersManager::getInstance()->getFatPlayer($l_ShopItem->getPlayer())->setSlot($l_ShopItem->getSlotName(), $l_ShopItem);
				})
			);
		}

		return $l_Ret;
	}

	public function getShopItemMenu(ShopItem $p_ShopItem):Window
	{
		$l_Player = $p_ShopItem->getPlayer();

		$l_Ret = new ButtonWindow($l_Player);
		$l_Ret->setTitle($p_ShopItem->getName());

		$l_Ret->setContent("Description....\nover multiple\nlines");

		$l_Ret->addPart((new Button())
			->setText("Equip")
			->setCallback(function () use ($p_ShopItem, $l_Player) {
				$p_ShopItem->equip();
				PlayersManager::getInstance()->getFatPlayer($l_Player)->setSlot($p_ShopItem->getSlotName(), $p_ShopItem);
			})
		);

		$l_Ret->addPart((new Button())
			->setText("Unequip")
			->setCallback(function () use ($p_ShopItem, $l_Player) {
				$p_ShopItem->unequip();
				PlayersManager::getInstance()->getFatPlayer($l_Player)->setSlot($p_ShopItem->getSlotName(), null);
			})
		);

		$l_Ret->addPart((new Button())
			->setText((new TextFormatter("window.return"))->asStringForPlayer($l_Player))
			->setCallback(function () use ($l_Player)
			{
				$this->getShopMenu($l_Player)->open();
			})
		);

		return $l_Ret;
	}
}