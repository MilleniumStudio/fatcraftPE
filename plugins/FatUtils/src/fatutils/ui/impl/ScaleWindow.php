<?php

namespace fatutils\ui\impl;

use fatutils\tools\Sidebar;
use fatutils\ui\windows\FormWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\ui\windows\parts\Slider;
use pocketmine\Player;

use fatutils\tools\TextFormatter;
use fatutils\players\PlayersManager;
use fatutils\players\FatPlayer;

class ScaleWindow
{
    public function __construct(Player $p_Player)
    {
        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);
        $l_ActualLanguage = (new TextFormatter("form.language.button." . strtolower(TextFormatter::$m_AvailableLanguages[$l_FatPlayer->getLanguage()])))->asStringForPlayer($p_Player);
        $l_Window = new FormWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("form.scale.title"))->asStringForPlayer($p_Player));
        //$l_Window->setContent((new TextFormatter("form.scale.content"))->addParam("language", $l_ActualLanguage)->asStringForPlayer($p_Player));

        $l_Window->addPart((new Slider())
            ->setText("Scale in %")
            ->setMin(50)
            ->setMax(150)
            ->setStep(1)
            ->setDefault($p_Player->getScale() * 100)
        );

        $l_Window->setCallback(function($p_Data) use ($p_Player) {
            echo ("callback !\n");
            foreach ($p_Data as $l_value)
            {
                $p_Player->setScale(intval($l_value) / 100);
            }
        });

        $l_Window->open();
    }
}

