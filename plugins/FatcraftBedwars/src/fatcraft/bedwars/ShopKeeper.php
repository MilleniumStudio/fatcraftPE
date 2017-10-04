<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 15/09/2017
 * Time: 17:17
 */

namespace fatcraft\bedwars;

use fatutils\tools\ClickableNPC;
use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\ui\windows\Window;
use pocketmine\level\Location;
use pocketmine\Player;

class ShopKeeper extends ClickableNPC
{
    public function __construct(Location $p_location)
    {
        parent::__construct($p_location);
        $this->setOnHitCallback(function($p_Player) {
            if ($p_Player instanceof Player)
                $this->getMainWindow($p_Player)->open();
        });
    }

    public function getMainWindow(Player $p_Player): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.title"))->asStringForPlayer($p_Player));

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.blocks.title"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player) {
                $this->getBlocksWindow($p_Player)->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.upgrades.title"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player) {
                $this->getUpgradesWindow($p_Player)->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.weapons.title"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player) {
                $this->getWeaponsWindow($p_Player)->open();
            })
        );
        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.armors.title"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player) {
                $this->getArmorsWindow($p_Player)->open();
            })
        );
        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.tools.title"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player) {
                $this->getToolsWindow($p_Player)->open();
            })
        );
        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.others.title"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player) {
                $this->getOthersWindow($p_Player)->open();
            })
        );

        return $l_Window;
    }

    //----------
    // ITEMS
    //----------
    //--> BLOCKS
    public function getBlocksWindow(Player $p_Player): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.items.blocks.title"))->asStringForPlayer($p_Player));

        //TODO foreach on all blocks
        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.blocks.something"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player, $l_Window) {
                echo $p_Player->getName() . " bought something\n";
                $l_Window->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function() use ($p_Player) {
                $this->getMainWindow($p_Player)->open();
            })
        );

        return $l_Window;
    }

    //--> WEAPONS
    public function getWeaponsWindow(Player $p_Player): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.items.weapons.title"))->asStringForPlayer($p_Player));

        //TODO foreach on all weapons
        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.weapons.something"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player, $l_Window) {
                echo $p_Player->getName() . " bought something\n";
                $l_Window->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function() use ($p_Player) {
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

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.armors.something"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player, $l_Window) {
                echo $p_Player->getName() . " bought something\n";
                $l_Window->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function() use ($p_Player) {
                $this->getMainWindow($p_Player)->open();
            })
        );

        return $l_Window;
    }

    //--> TOOLS
    public function getToolsWindow(Player $p_Player): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.items.tools.title"))->asStringForPlayer($p_Player));

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.tools.something"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player, $l_Window) {
                echo $p_Player->getName() . " bought something\n";
                $l_Window->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function() use ($p_Player) {
                $this->getMainWindow($p_Player)->open();
            })
        );

        return $l_Window;
    }

    //--> OTHERS
    public function getOthersWindow(Player $p_Player): Window
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("bedwars.shop.items.others.title"))->asStringForPlayer($p_Player));

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("bedwars.shop.items.others.something"))->asStringForPlayer($p_Player))
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player, $l_Window) {
                echo $p_Player->getName() . " bought something\n";
                $l_Window->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function() use ($p_Player) {
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
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() use ($p_Player, $l_Window) {
                echo $p_Player->getName() . " bought a forge upgrade\n";
                $l_Window->open();
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("window.return"))->asStringForPlayer($p_Player))
            ->setCallback(function() use ($p_Player) {
                $this->getMainWindow($p_Player)->open();
            })
        );

        return $l_Window;
    }
}