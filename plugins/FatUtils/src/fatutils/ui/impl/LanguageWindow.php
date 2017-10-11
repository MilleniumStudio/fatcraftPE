<?php

namespace fatutils\ui\impl;

use pocketmine\Player;

use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\players\PlayersManager;
use fatutils\players\FatPlayer;

class LanguageWindow
{
    private $m_FatPlayer;

    public function __construct(Player $p_Player)
    {
        $this->m_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);
        $l_ActualLanguage = (new TextFormatter("form.language.button." . strtolower(TextFormatter::$m_AvailableLanguages[$this->m_FatPlayer->getLanguage()])))->asStringForPlayer($p_Player);
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle((new TextFormatter("form.language.title"))->asStringForPlayer($p_Player));
        $l_Window->setContent((new TextFormatter("form.language.content"))->addParam("language", $l_ActualLanguage)->asStringForPlayer($p_Player));

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("form.language.button.en"))->asStringForPlayer($p_Player))
            ->setImage("http://www.crwflags.com/fotw/images/u/us-uk-friendship.gif")
            ->setCallback(function () use ($p_Player)
            {
                if($this->getFatPlayer()->setLanguage(TextFormatter::LANG_ID_EN))
                    $p_Player->sendMessage((new TextFormatter("language.apply"))->asStringForPlayer($p_Player));
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("form.language.button.fr"))->asStringForPlayer($p_Player))
            ->setImage("https://upload.wikimedia.org/wikipedia/commons/thumb/5/54/Civil_and_Naval_Ensign_of_France.svg/300px-Civil_and_Naval_Ensign_of_France.svg.png")
            ->setCallback(function () use ($p_Player)
            {
                if($this->getFatPlayer()->setLanguage(TextFormatter::LANG_ID_FR))
                    $p_Player->sendMessage((new TextFormatter("language.apply"))->asStringForPlayer($p_Player));
            })
        );

        $l_Window->addPart((new Button())
            ->setText((new TextFormatter("form.language.button.es"))->asStringForPlayer($p_Player))
            ->setImage("https://upload.wikimedia.org/wikipedia/commons/thumb/4/49/Flag_of_Spanish_language_%28ES-MX%29.svg/2000px-Flag_of_Spanish_language_%28ES-MX%29.svg.png")
            ->setCallback(function () use ($p_Player)
            {
                if($this->getFatPlayer()->setLanguage(TextFormatter::LANG_ID_ES))
                    $p_Player->sendMessage((new TextFormatter("language.apply"))->asStringForPlayer($p_Player));
            })
        );

        $l_Window->open();
    }

    public function getFatPlayer(): FatPlayer
    {
        return $this->m_FatPlayer;
    }
}

