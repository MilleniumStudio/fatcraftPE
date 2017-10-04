<?php

declare(strict_types=1);

namespace fatutils\ui;

use fatutils\FatUtils;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\FormWindow;
use fatutils\ui\windows\parts\Dropdown;
use fatutils\ui\windows\parts\Input;
use fatutils\ui\windows\parts\Label;
use fatutils\ui\windows\parts\Slider;
use fatutils\ui\windows\parts\StepSlider;
use fatutils\ui\windows\parts\Toggle;
use fatutils\ui\windows\Window;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class WindowsManager implements Listener
{
    private $m_WindowRegistry = [];

    private static $m_Instance;

    private function __construct()
    {
        WindowsManager::$m_Instance = $this;
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
    }

    public static function getInstance(): WindowsManager
    {
        if (is_null(WindowsManager::$m_Instance))
            WindowsManager::$m_Instance = new WindowsManager();

        return WindowsManager::$m_Instance;
    }

    public function registerPlayerWindow(Player $p_Player, Window $p_Window)
    {
        $this->m_WindowRegistry[$p_Player->getUniqueId()->toBinary()] = $p_Window;
    }

    public function onDataPacket(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        if ($packet instanceof ModalFormResponsePacket)
        {
            $l_PlayerIndex = $event->getPlayer()->getUniqueId()->toBinary();
            if (isset($this->m_WindowRegistry[$l_PlayerIndex]))
            {
                $l_JsonAsArray = json_decode($packet->formData, true);
                if (is_null($l_JsonAsArray))
                    return;

                $l_PlayerWindow = $this->m_WindowRegistry[$l_PlayerIndex];
                if ($l_PlayerWindow instanceof Window)
                {
                    var_dump($l_JsonAsArray, "==========");
                    if (!$l_PlayerWindow->handleResponse($l_JsonAsArray))
                        $l_PlayerWindow->open();
                }
            }
        }
    }

}
