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
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\ui\windows\Window;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class ShopManager
{
	private $m_Config = null;
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
		$this->m_Config = new Config(FatUtils::getInstance()->getDataFolder() . "shop.yml");

		if ($this->m_Config->exists("content"))
			$this->m_ShopItems = $this->m_Config->getNested("content");
	}

	public function getShopItemByKey(Player $p_Player, string $p_Key): ?ShopItem
	{
		if ($this->m_Config instanceof Config)
		{
			$l_Data = $this->m_Config->getNested("content." . $p_Key);
			if (is_array($l_Data))
				return ShopItem::createShopItem($p_Player, $p_Key, $l_Data);
		}

		return null;
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
				->setText((new TextFormatter("shop.cat.particles.title"))->asStringForPlayer($p_Player))
				->setCallback(function () use ($p_Player)
				{
					$this->getGenericCategory("particles", $p_Player)->open();
				})
			);
		}
		if (array_key_exists("pets", $l_ShopContent))
		{
			$l_Ret->addPart((new Button())
				->setText((new TextFormatter("shop.cat.pets.title"))->asStringForPlayer($p_Player))
				->setCallback(function () use ($p_Player)
				{
					$this->getGenericCategory("pets", $p_Player)->open();
				})
			);
		}

		if ($p_Player->isOp())
		{
			$l_Ret->addPart((new Button())
				->setText(TextFormat::GOLD . "★★★ GIVE ME MONEY ★★★")
				->setCallback(function () use ($p_Player, $l_Ret)
				{
					$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);
					$l_FatPlayer->addFatcoin(50);
					$l_FatPlayer->addFatbill(50);
					$l_Ret->open();
				})
			);
		}

		return $l_Ret;
	}

	public function getGenericCategory(string $p_CategoryName, Player $p_Player): Window
	{
		$l_Ret = new ButtonWindow($p_Player);
		$l_Ret->setTitle((new TextFormatter("shop.cat." . $p_CategoryName . ".title"))->asStringForPlayer($p_Player));
		$l_Ret->setContent((new TextFormatter("shop.cat." . $p_CategoryName . ".desc"))->asStringForPlayer($p_Player));
		$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

		foreach ($this->getShopContent()[$p_CategoryName] as $l_Key => $l_Pet)
		{
			$l_ShopItem = ShopItem::createShopItem($p_Player, $p_CategoryName . "." . $l_Key, $this->getShopContent()[$p_CategoryName][$l_Key]);
			$l_Ret->addPart((new Button())
				->setText(($l_FatPlayer->isBought($l_ShopItem) ? TextFormat::GREEN . "✔ " . TextFormat::DARK_GRAY . TextFormat::RESET : "") . (new TextFormatter($l_ShopItem->getName()))->asStringForPlayer($p_Player))
				->setImage($l_ShopItem->getImage())
				->setCallback(function () use ($p_CategoryName, $l_ShopItem)
				{
					$this->getShopItemMenu($p_CategoryName, $l_ShopItem)->open();
				})
			);
		}

		$l_Ret->addPart((new Button())
			->setText((new TextFormatter("window.return"))->asStringForFatPlayer($l_FatPlayer))
			->setCallback(function () use ($p_Player)
			{
				$this->getShopMenu($p_Player)->open();
			})
		);

		return $l_Ret;
	}

	public function getShopItemMenu(string $p_CategoryName, ShopItem $p_ShopItem): Window
	{
		$l_Player = $p_ShopItem->getPlayer();
		$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_ShopItem->getPlayer());

		$l_Ret = new ButtonWindow($l_Player);
		$l_Ret->setTitle((new TextFormatter($p_ShopItem->getName()))->asStringForFatPlayer($l_FatPlayer));

		if ($p_ShopItem->getDescription() != null)
			$l_Ret->setContent($p_ShopItem->getDescription());

		if (!$l_FatPlayer->isBought($p_ShopItem))
		{
			if ($p_ShopItem->getFatcoinPrice() > -1)
			{
				$l_Ret->addPart((new Button())
					->setText((new TextFormatter("shop.buy"))->asStringForFatPlayer($l_FatPlayer) . " (" . $p_ShopItem->getFatcoinPrice() . " " . (new TextFormatter("currency.fatcoin.short"))->asStringForFatPlayer($l_FatPlayer) . TextFormat::DARK_GRAY . TextFormat::RESET . ")")
					->setCallback(function () use ($p_CategoryName, $p_ShopItem, $l_FatPlayer)
					{
						if ($l_FatPlayer->getFatcoin() - $p_ShopItem->getFatcoinPrice() >= 0)
						{
							$l_FatPlayer->addFatcoin(-$p_ShopItem->getFatcoinPrice());
							$l_FatPlayer->addBoughtShopItem($p_ShopItem);
							$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("shop.bought", ["name" => new TextFormatter($p_ShopItem->getName())]))->asStringForFatPlayer($l_FatPlayer));
							Sidebar::getInstance()->updatePlayer($l_FatPlayer->getPlayer());
						} else
							$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("shop.notEnoughtMoney", ["name" => new TextFormatter($p_ShopItem->getName())]))->asStringForFatPlayer($l_FatPlayer));

						$this->getShopItemMenu($p_CategoryName, $p_ShopItem)->open();
					})
				);
			}
			if ($p_ShopItem->getFatbillPrice() > -1)
			{
				$l_Ret->addPart((new Button())
					->setText((new TextFormatter("shop.buy"))->asStringForFatPlayer($l_FatPlayer) . " (" . $p_ShopItem->getFatbillPrice() . " " . (new TextFormatter("currency.fatbill.short"))->asStringForFatPlayer($l_FatPlayer) . TextFormat::DARK_GRAY . TextFormat::RESET . ")")
					->setCallback(function () use ($p_CategoryName, $p_ShopItem, $l_FatPlayer)
					{
						if ($l_FatPlayer->getFatbill() - $p_ShopItem->getFatbillPrice() >= 0)
						{
							$l_FatPlayer->addFatbill(-$p_ShopItem->getFatbillPrice());
							$l_FatPlayer->addBoughtShopItem($p_ShopItem);
							Sidebar::getInstance()->updatePlayer($l_FatPlayer->getPlayer());
							$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("shop.bought", ["name" => new TextFormatter($p_ShopItem->getName())]))->asStringForFatPlayer($l_FatPlayer));
							Sidebar::getInstance()->updatePlayer($l_FatPlayer->getPlayer());
						} else
							$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("shop.notEnoughtMoney", ["name" => new TextFormatter($p_ShopItem->getName())]))->asStringForFatPlayer($l_FatPlayer));

						$this->getShopItemMenu($p_CategoryName, $p_ShopItem)->open();
					})
				);
			}
		} else
		{
			if (!$l_FatPlayer->isEquipped($p_ShopItem))
			{
				$l_Ret->addPart((new Button())
					->setText((new TextFormatter("shop.equip"))->asStringForFatPlayer($l_FatPlayer))
					->setImage($p_ShopItem->getImage())
					->setCallback(function () use ($p_ShopItem, $l_Player)
					{
						$this->equipShopItem($l_Player, $p_ShopItem);
					})
				);
			} else
			{
				$l_Ret->addPart((new Button())
					->setText((new TextFormatter("shop.unequip"))->asStringForFatPlayer($l_FatPlayer))
					->setImage($p_ShopItem->getImage())
					->setCallback(function () use ($p_ShopItem, $l_Player)
					{
						$l_SlotItem = PlayersManager::getInstance()->getFatPlayer($l_Player)->getSlot($p_ShopItem->getSlotName());
						if ($l_SlotItem instanceof ShopItem)
							$this->unequipShopItem($l_Player, $p_ShopItem);
					})
				);
			}
		}

		$l_Ret->addPart((new Button())
			->setText((new TextFormatter("window.return"))->asStringForPlayer($l_Player))
			->setCallback(function () use ($p_CategoryName, $l_Player)
			{
				$this->getGenericCategory($p_CategoryName, $l_Player)->open();
			})
		);

		return $l_Ret;
	}

	public function equipShopItem(Player $p_Player, ShopItem $p_ShopItem)
	{
		$p_ShopItem->equip();
		PlayersManager::getInstance()->getFatPlayer($p_Player)->setSlot($p_ShopItem->getSlotName(), $p_ShopItem);
	}

	public function unequipShopItem(Player $p_Player, ShopItem $p_ShopItem)
	{
		$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

		$l_CurrentSlotItem = $l_FatPlayer->getSlot($p_ShopItem->getSlotName());
		if ($l_CurrentSlotItem instanceof ShopItem && strcmp($l_CurrentSlotItem->getKey(), $p_ShopItem->getKey()) == 0)
		{
			$l_CurrentSlotItem->unequip();
			$l_FatPlayer->emptySlot($p_ShopItem->getSlotName());
		}
	}
}