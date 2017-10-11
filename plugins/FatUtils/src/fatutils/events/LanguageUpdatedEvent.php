<?php

namespace fatutils\events;

class LanguageUpdatedEvent extends \pocketmine\event\player\PlayerEvent
{

    private $m_Language;

    public function __construct(\pocketmine\Player $Player, int $Language)
    {
        parent::__cconstruct($Player);
        $this->m_Language = $Language;
    }

    public function getLanguage(): int
    {
        return $this->m_Language;
    }

}

