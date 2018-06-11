<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 13:51
 */

namespace fatcraft\lobby;

use fatutils\players\FatPlayer;
use fatutils\scores\ScoresManager;
use fatutils\shop\paintball\Paintball;
use fatutils\shop\ShopItem;
use fatutils\shop\ShopManager;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use pocketmine\utils\TextFormat;

class LootBox
{
    private $m_fatPlayer;
    private $m_lootArray = array();

    public function __construct(FatPlayer $p_fatPlayer)
    {
        $this->m_fatPlayer = $p_fatPlayer;
    }

    public function rollItem()
    {
        //$this->m_fatPlayer->getPlayer()->addXpLevels(1);
        $l_Player = $this->m_fatPlayer->getPlayer();
        //ScoresManager::getInstance()->giveGlobalXpRewardToPlayer($this->m_fatPlayer->getPlayer()->getUniqueId(), 4);
        $rolledValue = rand(1, 100);
        $l_ShopItem = null;
        if ($rolledValue < 50)
        {
            $secondRolledValue = rand(0, 15);
            $l_ShopItemKey = "";

            foreach (ShopManager::getInstance()->getShopContent()[ShopManager::CATEGORY_PAINTBALL] as $l_key => $l_value)
            {
                if ($secondRolledValue == 0)
                {
                    $l_ShopItemKey = $l_key;
                    break;
                }
                $secondRolledValue--;
            }

            $l_Player->sendMessage("You've earned : " . TextFormat::GOLD . (new TextFormatter("shop.items." . ShopManager::CATEGORY_PAINTBALL . "." . $l_ShopItemKey , []
            ))->asStringForPlayer($l_Player));

            echo ("shop item : " . ShopManager::CATEGORY_PAINTBALL . "." . $l_ShopItemKey. "\n");
            $l_ShopItem = ShopItem::createShopItem($this->m_fatPlayer->getPlayer(), ShopManager::CATEGORY_PAINTBALL . "." . $l_ShopItemKey, ShopManager::getInstance()->getShopContent()[ShopManager::CATEGORY_PAINTBALL][$l_ShopItemKey]);
            $this->m_fatPlayer->addAmmountableBoughtShopItem($l_ShopItem, -3, -3, 64);
            return;
        }
        if ($rolledValue < 95)
        {
            $l_ShopItemKey = "";

            $secondRolledValue = rand(0, 1);
            if ($secondRolledValue == 0) // pets
            {
                $thirdRolledValue = rand(0, count(ShopManager::getInstance()->getShopContent()[ShopManager::CATEGORY_PET]) - 1);

                foreach (ShopManager::getInstance()->getShopContent()[ShopManager::CATEGORY_PET] as $l_key => $l_value)
                {
                    if ($thirdRolledValue == 0)
                    {
                        $l_ShopItemKey = $l_key;
                        break;
                    }
                    $thirdRolledValue--;
                }
                echo ("shop item : " . ShopManager::CATEGORY_PET . "." . $l_ShopItemKey. "\n");
                $l_Player->sendMessage("You've earned : " . TextFormat::GOLD . (new TextFormatter("shop.items." . ShopManager::CATEGORY_PET . "." . $l_ShopItemKey , []
                ))->asStringForPlayer($l_Player));

                $l_ShopItem = ShopItem::createShopItem($this->m_fatPlayer->getPlayer(), ShopManager::CATEGORY_PET . "." . $l_ShopItemKey, ShopManager::getInstance()->getShopContent()[ShopManager::CATEGORY_PET][$l_ShopItemKey]);
            }
            else // particles
            {
                $thirdRolledValue = rand(0, count(ShopManager::getInstance()->getShopContent()[ShopManager::CATEGORY_PARTICLE]) - 1);
                foreach (ShopManager::getInstance()->getShopContent()[ShopManager::CATEGORY_PARTICLE] as $l_key => $l_value)
                {
                    if ($thirdRolledValue == 0)
                    {
                        $l_ShopItemKey = $l_key;
                        break;
                    }
                    $thirdRolledValue--;
                }
                echo ("shop item : " . ShopManager::CATEGORY_PARTICLE . "." . $l_ShopItemKey. "\n");
                $l_Player->sendMessage("You've earned : " . TextFormat::GOLD . (new TextFormatter("shop.items." . ShopManager::CATEGORY_PARTICLE . "." . $l_ShopItemKey , []
                ))->asStringForPlayer($l_Player));
                $l_ShopItem = ShopItem::createShopItem($this->m_fatPlayer->getPlayer(), ShopManager::CATEGORY_PARTICLE . "." . $l_ShopItemKey, ShopManager::getInstance()->getShopContent()[ShopManager::CATEGORY_PARTICLE][$l_ShopItemKey]);
            }
            if ($l_ShopItem != null)
            {
                if (!$this->m_fatPlayer->isBought($l_ShopItem))
                    $this->m_fatPlayer->addBoughtShopItem($l_ShopItem, -3, -3);
                else
                {
                    $l_FSvalue = $l_ShopItem->getFatsilverPrice();
                    if ($l_FSvalue == -1)
                    {
                        $this->m_fatPlayer->addFatsilver(800);
                        echo("FatSilver : 800\n");
                    }
                    else
                    {
                        $this->m_fatPlayer->addFatsilver($l_ShopItem->getFatsilverPrice() / 3);
                        echo ("FatSilver : " . ($l_ShopItem->getFatsilverPrice() / 3) . "\n");
                    }
                    Sidebar::getInstance()->updatePlayer($this->m_fatPlayer->getPlayer());
                }
            }
            return;
        }
        if ($rolledValue <= 100)
        {
            $secondRolledValue = rand(1, 100);
            if ($secondRolledValue < 55)
            {
                if ($this->m_fatPlayer->getVipRank() >= 0)
                {
                    $this->m_fatPlayer->addFatgold(33);
                    echo ("33 Fatgolds\n");
                }
                else
                {
                    $this->m_fatPlayer->setPermissionGroup("Hero");
                    $l_Player->sendMessage("You are now a Fat" . TextFormat::GREEN . " Hero.");
                    echo("Rank : Hero\n");
                }
                Sidebar::getInstance()->updatePlayer($this->m_fatPlayer->getPlayer());
                return;
            }
            if ($secondRolledValue < 90)
            {
                if ($this->m_fatPlayer->getVipRank() >= 1)
                {
                    $this->m_fatPlayer->addFatgold(66);
                    echo ("66 Fatgolds\n");
                }
                else
                {
                    $this->m_fatPlayer->setPermissionGroup("Titan");
                    $l_Player->sendMessage("You are now a Fat" . TextFormat::RED . " Titan.");
                    echo("Rank : Titan\n");
                }
                Sidebar::getInstance()->updatePlayer($this->m_fatPlayer->getPlayer());
                return;
            }
            if ($secondRolledValue <= 100)
            {
                if ($this->m_fatPlayer->getVipRank() >= 2)
                {
                    $this->m_fatPlayer->addFatgold(66);
                    echo ("165 Fatgolds\n");
                }
                else
                {
                    $this->m_fatPlayer->addFatgold(165);
                    $this->m_fatPlayer->setPermissionGroup("Legend");
                    $l_Player->sendMessage("You are now a Fat" . TextFormat::GOLD . " Legend.");
                    echo("Rank : Legend\n");
                }
                Sidebar::getInstance()->updatePlayer($this->m_fatPlayer->getPlayer());
                return;
            }
        }
    }
}