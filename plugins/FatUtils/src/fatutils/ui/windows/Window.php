<?php

declare(strict_types = 1);

namespace fatutils\ui\windows;

use fatutils\tools\TextFormatter;
use fatutils\ui\windows\parts\UiPart;
use fatutils\ui\WindowsManager;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

abstract class Window
{
    /** @var Player */
    private $m_Player = null;

    /** @var array */
    protected $m_Data = [];
    protected $m_Parts = [];

    public function __construct(Player $player)
    {
        $this->m_Player = $player;
    }

    protected function setType(string $p_Type)
    {
        $this->getData()["type"] = $p_Type;
    }

    public function setTitle(string $p_Title): Window
    {
        $this->getData()["title"] = $p_Title;
        return $this;
    }

    protected function _addPart(UiPart $p_Part)
    {
        $this->m_Parts[] = $p_Part;
    }

    /**
     * @return array
     */
    public function getParts(): array
    {
        return $this->m_Parts;
    }

    /**
     * @return string
     */
    public function getAsJson(): string
    {
        return json_encode($this->m_Data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->m_Data;
    }

    /**
     * @return TextFormatter|string
     */
    public function getTitle()
    {
        return $this->m_Title;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->m_Player;
    }

    public function open()
    {
        $l_Packet = new ModalFormRequestPacket();
        $l_Packet->formId = $this->getWindowIdFor($this->getWindowId());
        $l_Packet->formData = $this->getAsJson();
        $this->getPlayer()->dataPacket($l_Packet);

        WindowsManager::getInstance()->registerPlayerWindow($this->getPlayer(), $this);
    }

    /**
     * Don't ask i don't know...
     *
     * @param int $windowId
     * @return int
     */
    private function getWindowIdFor(int $windowId): int
    {
        if ($windowId >= 3200)
        {
            return $windowId - 3200;
        }
        return 3200 + $windowId;
    }

    protected abstract function getWindowId(): int;

    public abstract function handleResponse(array $packet): bool;
}
