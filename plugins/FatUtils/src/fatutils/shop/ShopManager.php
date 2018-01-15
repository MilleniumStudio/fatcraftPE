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
use fatutils\tools\schedulers\DelayedExec;
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

	public static $m_OptionAutoEquipSavedItems = false;

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
		FatUtils::getInstance()->saveResource("shop.yml");
		$this->m_Config = new Config(FatUtils::getInstance()->getDataFolder() . "shop.yml");

		if ($this->m_Config->exists("content"))
			$this->m_ShopItems = $this->m_Config->getNested("content");
	}

	public function reload()
	{
		$this->init();
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

	public function equipItems(Player $p_Player, array $p_ItemsId)
	{
		$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);
		foreach ($p_ItemsId as $l_Key)
		{
			$l_ShopItem = ShopManager::getInstance()->getShopItemByKey($p_Player, $l_Key);
			if (!is_null($l_ShopItem))
			{
				$l_ShopItem->equip();
				$l_FatPlayer->setSlot($l_ShopItem->getSlotName(), $l_ShopItem, false);
			}
		}
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
					$l_FatPlayer->addFatsilver(500);
					$l_FatPlayer->addFatgold(500);
					$l_Ret->open();
					Sidebar::getInstance()->updatePlayer($p_Player);
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

		$l_EquippedText = new TextFormatter("shop.equpped");
		$l_BuyableText = new TextFormatter("shop.buyable");
		$l_UnbuyableText = new TextFormatter("shop.unbuyable");

		$l_Ret->addPart((new Button())
			->setText((new TextFormatter("window.return"))->asStringForFatPlayer($l_FatPlayer))
			->setCallback(function () use ($p_Player)
			{
				$this->getShopMenu($p_Player)->open();
			})
		);

		foreach ($this->getShopContent()[$p_CategoryName] as $l_Key => $l_Pet)
		{
			$l_ShopItem = ShopItem::createShopItem($p_Player, $p_CategoryName . "." . $l_Key, $this->getShopContent()[$p_CategoryName][$l_Key]);

			$l_TopText = (new TextFormatter($l_ShopItem->getName()))->asStringForFatPlayer($l_FatPlayer);
			$l_BottomText = "";

			if (!$l_FatPlayer->isBought($l_ShopItem))
			{
				if (($l_ShopItem->getFatsilverPrice() > -1 && ($l_FatPlayer->getFatsilver() - $l_ShopItem->getFatsilverPrice() >= 0))
					|| ($l_ShopItem->getFatgoldPrice() > -1 && ($l_FatPlayer->getFatgold() - $l_ShopItem->getFatgoldPrice() >= 0)))
					$l_BottomText .= $l_BuyableText->asStringForFatPlayer($l_FatPlayer);
				else
					$l_BottomText .= $l_UnbuyableText->asStringForFatPlayer($l_FatPlayer);
			} else if ($l_FatPlayer->isEquipped($l_ShopItem))
			{
				$l_BottomText .= $l_EquippedText->asStringForFatPlayer($l_FatPlayer);
			}

			$l_Ret->addPart((new Button())
				->setText($l_TopText . (strlen($l_BottomText) > 0 ? "\n" . $l_BottomText : ""))
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
		$l_Player = $p_ShopItem->getEntity();
		if ($l_Player instanceof Player)
		{
			$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($l_Player);

			$l_Ret = new ButtonWindow($l_Player);
			$l_Ret->setTitle((new TextFormatter($p_ShopItem->getName()))->asStringForFatPlayer($l_FatPlayer));

			if ($p_ShopItem->getDescription() != null)
				$l_Ret->setContent((new TextFormatter($p_ShopItem->getDescription()))->asStringForFatPlayer($l_FatPlayer));

			if (!$l_FatPlayer->isBought($p_ShopItem))
			{
				if ($p_ShopItem->getFatsilverPrice() > -1)
				{
					$l_Ret->addPart((new Button())
						->setText((new TextFormatter("shop.buy"))->asStringForFatPlayer($l_FatPlayer) .
							" (" . $p_ShopItem->getFatsilverPrice() . " " . (new TextFormatter("currency.fatsilver.short"))->asStringForFatPlayer($l_FatPlayer) . TextFormat::DARK_GRAY . ")" .
							($l_FatPlayer->getFatsilver() - $p_ShopItem->getFatsilverPrice() < 0 ? "\n" . (new TextFormatter("shop.moneyMissing", [
									"amount" => abs($l_FatPlayer->getFatsilver() - $p_ShopItem->getFatsilverPrice()),
									"money" => new TextFormatter("currency.fatsilver.short")
								]))->asStringForFatPlayer($l_FatPlayer) : "")
						)
						->setCallback(function () use ($p_CategoryName, $p_ShopItem, $l_FatPlayer)
						{
							if ($l_FatPlayer->getFatsilver() - $p_ShopItem->getFatsilverPrice() >= 0)
							{
								$l_FatPlayer->addFatsilver(-$p_ShopItem->getFatsilverPrice());
								$l_FatPlayer->addBoughtShopItem($p_ShopItem);
								$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("shop.bought", ["name" => new TextFormatter($p_ShopItem->getName())]))->asStringForFatPlayer($l_FatPlayer));
								Sidebar::getInstance()->updatePlayer($l_FatPlayer->getPlayer());
							} else
								$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("shop.notEnoughtMoney", ["name" => new TextFormatter($p_ShopItem->getName())]))->asStringForFatPlayer($l_FatPlayer));

							$this->getShopItemMenu($p_CategoryName, $p_ShopItem)->open();
						})
					);
				}
				if ($p_ShopItem->getFatgoldPrice() > -1)
				{
					$l_Ret->addPart((new Button())
						->setText((new TextFormatter("shop.buy"))->asStringForFatPlayer($l_FatPlayer) .
							" (" . $p_ShopItem->getFatgoldPrice() . " " . (new TextFormatter("currency.fatgold.short"))->asStringForFatPlayer($l_FatPlayer) . TextFormat::DARK_GRAY . ")" .
							($l_FatPlayer->getFatgold() - $p_ShopItem->getFatgoldPrice() < 0 ? "\n" . (new TextFormatter("shop.moneyMissing", [
									"amount" => abs($l_FatPlayer->getFatgold() - $p_ShopItem->getFatgoldPrice()),
									"money" => new TextFormatter("currency.fatgold.short")
								]))->asStringForFatPlayer($l_FatPlayer) : ""))
						->setCallback(function () use ($p_CategoryName, $p_ShopItem, $l_FatPlayer)
						{
							if ($l_FatPlayer->getFatgold() - $p_ShopItem->getFatgoldPrice() >= 0)
							{
								$l_FatPlayer->addFatgold(-$p_ShopItem->getFatgoldPrice());
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

				$l_Ret->addPart((new Button())
					->setText((new TextFormatter("shop.preview"))->asStringForFatPlayer($l_FatPlayer))
					->setCallback(function () use ($p_CategoryName, $p_ShopItem, $l_FatPlayer)
					{
						$p_ShopItem->equip();
						$l_FatPlayer->setPreviewing(true);
						new DelayedExec(function () use ($p_CategoryName, $p_ShopItem, $l_FatPlayer)
						{
							if ($l_FatPlayer->getPlayer()->isOnline())
							{
								$p_ShopItem->unequip();
								if ($l_FatPlayer->isPreviewing())
								{
									$this->getShopItemMenu($p_CategoryName, $p_ShopItem)->open();
									$l_FatPlayer->setPreviewing(false);
									return;
								}
							}
						}, 4 * 20);
					})
				);
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
		return null;
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