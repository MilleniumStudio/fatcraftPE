<?php

namespace fatutils\ui\impl;

use fatutils\tools\Sidebar;
use pocketmine\Player;

use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\players\PlayersManager;
use fatutils\players\FatPlayer;

class LanguageWindow
{
    public function __construct(Player $p_Player)
    {
        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);
        $l_ActualLanguage = (new TextFormatter("form.language.button." . strtolower(TextFormatter::$m_AvailableLanguages[$l_FatPlayer->getLanguage()])))->asStringForPlayer($p_Player);
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("form.language.title"))->asStringForPlayer($p_Player));
        $l_Window->setContent((new TextFormatter("form.language.content"))->addParam("language", $l_ActualLanguage)->asStringForPlayer($p_Player));

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("form.language.button.en"))->asStringForPlayer($p_Player))
            ->setImage("http://www.crwflags.com/fotw/images/u/us-uk-friendship.gif")
            ->setCallback(function () use ($l_FatPlayer)
            {
                $l_FatPlayer->setLanguage(TextFormatter::LANG_ID_EN);
				Sidebar::getInstance()->updatePlayer($l_FatPlayer->getPlayer());
                $l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("language.apply"))->asStringForFatPlayer($l_FatPlayer));
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("form.language.button.fr"))->asStringForFatPlayer($l_FatPlayer))
            ->setImage("https://upload.wikimedia.org/wikipedia/commons/thumb/5/54/Civil_and_Naval_Ensign_of_France.svg/300px-Civil_and_Naval_Ensign_of_France.svg.png")
            ->setCallback(function () use ($l_FatPlayer)
            {
                $l_FatPlayer->setLanguage(TextFormatter::LANG_ID_FR);
                Sidebar::getInstance()->updatePlayer($l_FatPlayer->getPlayer());
                $l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("language.apply"))->asStringForFatPlayer($l_FatPlayer));
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("form.language.button.es"))->asStringForFatPlayer($l_FatPlayer))
            ->setImage("https://upload.wikimedia.org/wikipedia/commons/thumb/4/49/Flag_of_Spanish_language_%28ES-MX%29.svg/2000px-Flag_of_Spanish_language_%28ES-MX%29.svg.png")
            ->setCallback(function () use ($l_FatPlayer)
            {
                $l_FatPlayer->setLanguage(TextFormatter::LANG_ID_ES);
				Sidebar::getInstance()->updatePlayer($l_FatPlayer->getPlayer());
				$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("language.apply"))->asStringForFatPlayer($l_FatPlayer));
            })
        );

        $l_Window->open();
    }
}

