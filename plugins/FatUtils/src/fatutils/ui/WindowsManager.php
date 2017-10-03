<?php

declare(strict_types = 1);

namespace fatutils\ui;

use fatutils\FatUtils;
use fatutils\ui\windows\ButtonMenuWindow;
use fatutils\ui\windows\InputMenuWindow;
use fatutils\ui\windows\Window;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class WindowsManager implements Listener
{

    private static $m_Instance;

    const WINDOW_BUTTON_MENU = 0;
    const WINDOW_INPUT_MENU = 1;

    /** @var string[] */
    private $types = [
        ButtonMenuWindow::class,
        InputMenuWindow::class
    ];

    private function __construct()
    {
        WindowsManager::$m_Instance = $this;
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
    }

    public static function getInstance() : WindowsManager
    {
        if (WindowsManager::$m_Instance == NULL)
        {
            WindowsManager::$m_Instance = new WindowsManager();
        }
        return WindowsManager::$m_Instance;
    }

    /**
     * @param int    $windowId
     * @param Loader $loader
     * @param Player $player
     *
     * @return string
     */
    public function getWindowJson(int $windowId, FatUtils $loader, Player $player): string
    {
        return $this->getWindow($windowId, $loader, $player)->getJson();
    }

    /**
     * @param int    $windowId
     * @param Loader $loader
     * @param Player $player
     *
     * @return Window
     */
    public function getWindow(int $windowId, FatUtils $loader, Player $player): Window
    {
        if (!isset($this->types[$windowId]))
        {
            throw new \OutOfBoundsException("Tried to get window of non-existing window ID.");
        }
        return new $this->types[$windowId]($loader, $player);
    }

    /**
     * @param int $windowId
     *
     * @return bool
     */
    public function isInRange(int $windowId): bool
    {
        if (isset($this->types[$windowId]) || isset($this->types[$windowId + 3200]))
        {
            return true;
        }
        return false;
    }

    /**
     * @param int $windowId
     *
     * @return int
     */
    public function getWindowIdFor(int $windowId): int
    {
        if ($windowId >= 3200)
        {
            return $windowId - 3200;
        }
        return 3200 + $windowId;
    }

    public function sendMenu($p_Player, $p_MenuId)
    {
        $l_Packet = new ModalFormRequestPacket();
        $l_Packet->formId = $this->getWindowIdFor($p_MenuId);
        $l_Packet->formData = $this->getWindowJson($p_MenuId, FatUtils::getInstance(), $p_Player);
        $p_Player->dataPacket($l_Packet);
    }

    public function onDataPacket(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        if ($packet instanceof ModalFormResponsePacket)
        {
            if (json_decode($packet->formData, true) === null)
            {
                return;
            }
            $packet->formId = $this->getWindowIdFor($packet->formId);
            if (!$this->isInRange($packet->formId))
            {
                return;
            }
            $window = $this->getWindow($packet->formId, FatUtils::getInstance(), $event->getPlayer());
            $window->handle($packet);
        }
    }

}
