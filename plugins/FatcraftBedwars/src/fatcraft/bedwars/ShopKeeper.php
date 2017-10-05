<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 15/09/2017
 * Time: 17:17
 */

namespace fatcraft\bedwars;

use fatutils\players\PlayersManager;
use fatutils\teams\TeamsManager;
use fatutils\tools\ClickableNPC;
use fatutils\tools\ColorUtils;
use fatutils\tools\ItemUtils;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\ui\windows\Window;
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ShopKeeper extends ClickableNPC
{
    const IMAGE_PLACEHOLDER = "https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png";

    private static $m_ShopContent = null;

    public function __construct(Location $p_location)
    {
        parent::__construct($p_location);

        if (is_null(self::$m_ShopContent))
        {
            Bedwars::getInstance()->getLogger()->info("Loading Shop content...");
            self::$m_ShopContent = Bedwars::getInstance()->getConfig()->get("shop");
//            var_dump(self::$m_ShopContent);
        }

        $this->setOnHitCallback(function ($p_Player)
        {
            if ($p_Player instanceof Player)
                $this->getMainWindow($p_Player)->open();
        });
    }

    public function getMainWindow(Player $p_Player): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.title"))->asStringForPlayer($p_Player));

        if (isset(self::$m_ShopContent["blocks"]))
        {
            $l_Window->addPart((new Button())
                ->setText((new TextFormatter("bedwars.shop.items.blocks.title"))->asStringForPlayer($p_Player))
                ->setImage(ShopKeeper::IMAGE_PLACEHOLDER)
                ->setCallback(function () use ($p_Player)
                {
                    $this->getGenericItemWindow($p_Player, "blocks")->open();
                })
            );
        }

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.upgrades.title"))->asStringForPlayer($p_Player))
            ->setImage(ShopKeeper::IMAGE_PLACEHOLDER)
            ->setCallback(function () use ($p_Player)
            {
                $this->getUpgradesWindow($p_Player)->open();
            })
        );

        if (isset(self::$m_ShopContent["weapons"]))
        {
            $l_Window->addPart((new Button())
                ->setText((new TextFormatter("bedwars.shop.items.weapons.title"))->asStringForPlayer($p_Player))
                ->setImage(ShopKeeper::IMAGE_PLACEHOLDER)
                ->setCallback(function () use ($p_Player)
                {
                    $this->getGenericItemWindow($p_Player, "weapons")->open();
                })
            );
        }

        if (isset(self::$m_ShopContent["armors"]))
        {
            $l_Window->addPart((new Button())
                ->setText((new TextFormatter("bedwars.shop.items.armors.title"))->asStringForPlayer($p_Player))
                ->setImage(ShopKeeper::IMAGE_PLACEHOLDER)
                ->setCallback(function () use ($p_Player)
                {
                    $this->getArmorsWindow($p_Player)->open();
                })
            );
        }

        if (isset(self::$m_ShopContent["tools"]))
        {
            $l_Window->addPart((new Button())
                ->setText((new TextFormatter("bedwars.shop.items.tools.title"))->asStringForPlayer($p_Player))
                ->setImage(ShopKeeper::IMAGE_PLACEHOLDER)
                ->setCallback(function () use ($p_Player)
                {
                    $this->getGenericItemWindow($p_Player, "tools")->open();
                })
            );
        }

        if (isset(self::$m_ShopContent["others"]))
        {
            $l_Window->addPart((new Button())
                ->setText((new TextFormatter("bedwars.shop.items.others.title"))->asStringForPlayer($p_Player))
                ->setImage(ShopKeeper::IMAGE_PLACEHOLDER)
                ->setCallback(function () use ($p_Player)
                {
                    $this->getGenericItemWindow($p_Player, "others")->open();
                })
            );
        }

        if ($p_Player->isOp())
        {
            $l_Window->addPart((new Button())
                ->setText(TextFormat::YELLOW . "★★★ Give me money ★★★")
                ->setCallback(function () use ($p_Player, $l_Window)
                {
                    Bedwars::getInstance()->modPlayerIron($p_Player, 50);
                    Bedwars::getInstance()->modPlayerGold($p_Player, 50);
                    Bedwars::getInstance()->modPlayerDiamond($p_Player, 50);
                    $l_Window->open();
                })
            );
        }

        return $l_Window;
    }

    private function buy(Player $p_Player, array $p_ConfigPart)
    {
        if (isset($p_ConfigPart["ironPrice"]))
        {
            $ironPrice = $p_ConfigPart["ironPrice"];
            if (Bedwars::getInstance()->getPlayerIron($p_Player) >= $ironPrice)
            {
                Bedwars::getInstance()->modPlayerIron($p_Player, -$ironPrice);
                return true;
            }
        } else if (isset($p_ConfigPart["goldPrice"]))
        {
            $goldPrice = $p_ConfigPart["goldPrice"];
            if (Bedwars::getInstance()->getPlayerGold($p_Player) >= $goldPrice)
            {
                Bedwars::getInstance()->modPlayerGold($p_Player, -$goldPrice);
                return true;
            }
        } else if (isset($p_ConfigPart["diamondPrice"]))
        {
            $diamondPrice = $p_ConfigPart["diamondPrice"];
            if (Bedwars::getInstance()->getPlayerDiamond($p_Player) >= $diamondPrice)
            {
                Bedwars::getInstance()->modPlayerDiamond($p_Player, -$diamondPrice);
                return true;
            }
        }

        return false;
    }

    private function getPrice(array $p_ConfigPart): string
    {
        if (isset($p_ConfigPart["ironPrice"]))
            return $p_ConfigPart["ironPrice"] . " §7I§r";
        if (isset($p_ConfigPart["goldPrice"]))
            return $p_ConfigPart["goldPrice"] . " §6G§r";
        if (isset($p_ConfigPart["diamondPrice"]))
            return $p_ConfigPart["diamondPrice"] . " §bD§r";
        return "";
    }

    //----------
    // ITEMS
    //----------
    //--> GENERIC
    public function getGenericItemWindow(Player $p_Player, string $p_Base): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.items.$p_Base.title"))->asStringForPlayer($p_Player));

        if (isset(self::$m_ShopContent[$p_Base]))
        {
            foreach (self::$m_ShopContent[$p_Base] as $l_Key => $l_Item)
            {
                $l_ImageUrl = ShopKeeper::IMAGE_PLACEHOLDER;
                if (isset(self::$m_ShopContent[$p_Base][$l_Key]["imageUrl"]))
                    $l_ImageUrl = self::$m_ShopContent[$p_Base][$l_Key]["imageUrl"];

                $l_Window->addPart((new Button())
                    ->setText((new TextFormatter("bedwars.shop.items.$p_Base." . $l_Key))->asStringForPlayer($p_Player) . " (" . $this->getPrice($l_Item) . ")")
                    ->setImage($l_ImageUrl)
                    ->setCallback(function () use ($p_Player, $p_Base, $l_Window, $l_Key, $l_Item)
                    {
                        if ($this->buy($p_Player, $l_Item))
                        {
                            $l_Item = ItemUtils::getItemFromRaw(self::$m_ShopContent[$p_Base][$l_Key]["rawItem"]);

                            $l_Color = ColorUtils::WHITE;
                            $l_Team = TeamsManager::getInstance()->getPlayerTeam($p_Player);
                            if (!is_null($l_Team))
                                $l_Color = $l_Team->getColor();

                            $l_Item = ItemUtils::getColoredItemIfColorable($l_Item, $l_Color);

                            $p_Player->getInventory()->addItem($l_Item);
                            $p_Player->sendMessage((new TextFormatter("bedwars.shop.bought", ["name" => new TextFormatter("bedwars.shop.items.$p_Base." . $l_Key)]))->asStringForPlayer($p_Player));
                            Bedwars::getInstance()->getLogger()->info($p_Player->getName() . " bought " . $l_Key . "\n");
                        }
                        $l_Window->open();
                    })
                );
            }
        }

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function () use ($p_Player)
            {
                $this->getMainWindow($p_Player)->open();
            })
        );

        return $l_Window;
    }

    //--> ARMORS
    public function getArmorsWindow(Player $p_Player): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.items.armors.title"))->asStringForPlayer($p_Player));

        if (isset(self::$m_ShopContent["armors"]))
        {
            foreach (self::$m_ShopContent["armors"] as $l_Key => $l_Item)
            {
                $l_ImageUrl = ShopKeeper::IMAGE_PLACEHOLDER;
                if (isset(self::$m_ShopContent["armors"][$l_Key]["imageUrl"]))
                    $l_ImageUrl = self::$m_ShopContent["armors"][$l_Key]["imageUrl"];

                $l_Window->addPart((new Button())
                    ->setText((new TextFormatter("bedwars.shop.items.armors." . $l_Key))->asStringForPlayer($p_Player) . " (" . $this->getPrice($l_Item) . ")")
                    ->setImage($l_ImageUrl)
                    ->setCallback(function () use ($p_Player, $l_Window, $l_Key, $l_Item)
                    {
                        if ($this->buy($p_Player, $l_Item))
                        {
                            $l_Color = ColorUtils::WHITE;
                            $l_Team = TeamsManager::getInstance()->getPlayerTeam($p_Player);
                            if (!is_null($l_Team))
                                $l_Color = $l_Team->getColor();

                            if (isset(self::$m_ShopContent["armors"][$l_Key]["helmet"]))
                            {
                                $l_Item = ItemUtils::getItemFromRaw(self::$m_ShopContent["armors"][$l_Key]["helmet"]);
                                if (ItemUtils::isHelmet($l_Item->getId()))
                                    $p_Player->getInventory()->setHelmet(ItemUtils::getColoredItemIfColorable($l_Item, $l_Color));
                            }
                            if (isset(self::$m_ShopContent["armors"][$l_Key]["chestplate"]))
                            {
                                $l_Item = ItemUtils::getItemFromRaw(self::$m_ShopContent["armors"][$l_Key]["chestplate"]);
                                if (ItemUtils::isChestplate($l_Item->getId()))
                                    $p_Player->getInventory()->setChestplate(ItemUtils::getColoredItemIfColorable($l_Item, $l_Color));
                            }
                            if (isset(self::$m_ShopContent["armors"][$l_Key]["leggings"]))
                            {
                                $l_Item = ItemUtils::getItemFromRaw(self::$m_ShopContent["armors"][$l_Key]["leggings"]);
                                if (ItemUtils::isLeggings($l_Item->getId()))
                                    $p_Player->getInventory()->setLeggings(ItemUtils::getColoredItemIfColorable($l_Item, $l_Color));
                            }
                            if (isset(self::$m_ShopContent["armors"][$l_Key]["boots"]))
                            {
                                $l_Item = ItemUtils::getItemFromRaw(self::$m_ShopContent["armors"][$l_Key]["boots"]);
                                if (ItemUtils::isBoots($l_Item->getId()))
                                    $p_Player->getInventory()->setBoots(ItemUtils::getColoredItemIfColorable($l_Item, $l_Color));
                            }

                            $p_Player->sendMessage((new TextFormatter("bedwars.shop.bought", ["name" => new TextFormatter("bedwars.shop.items.armors." . $l_Key)]))->asStringForPlayer($p_Player));
                            Bedwars::getInstance()->getLogger()->info($p_Player->getName() . " bought " . $l_Key . "\n");
                        }
                        $l_Window->open();
                    })
                );
            }
        }
        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function () use ($p_Player)
            {
                $this->getMainWindow($p_Player)->open();
            })
        );

        return $l_Window;
    }

    //-------------
    // UPGRADES
    //-------------
    public function getUpgradesWindow(Player $p_Player): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.upgrades.title"))->asStringForPlayer($p_Player));

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.upgrades.forge"))->asStringForPlayer($p_Player))
            ->setImage(ShopKeeper::IMAGE_PLACEHOLDER)
            ->setCallback(function () use ($p_Player, $l_Window)
            {
//                Bedwars::getInstance()->upgradeIronForge($p_Player->get)
//                $p_Player->sendMessage((new TextFormatter("bedwars.shop.upgrades.forge.upgraded", ["type", "Iron"]))->asStringForPlayer($p_Player));
//                echo $p_Player->getName() . " bought a forge upgrade\n";
                $p_Player->sendMessage("Nothing yet i'm afraid...");
                $l_Window->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function () use ($p_Player)
            {
                $this->getMainWindow($p_Player)->open();
            })
        );

        return $l_Window;
    }
}