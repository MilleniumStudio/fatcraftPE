<?php

namespace fatutils\events;

class LanguageUpdatedEvent extends \pocketmine\event\player\PlayerEvent
{
    public static $handlerList = null;

    private $m_Language;

    public function __construct(\pocketmine\Player $Player, int $Language)
    {
        $this->player = $Player;
        $this->m_Language = $Language;
    }

    public function getLanguage(): int
    {
        return $this->m_Language;
    }

}