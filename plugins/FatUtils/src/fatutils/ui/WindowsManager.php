<?php

declare(strict_types=1);

namespace fatutils\ui;

use fatutils\FatUtils;
use fatutils\ui\windows\Window;
use pocketmine\Player;
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

    /**
     * Use this function if you want to be sure that after a point, no old window will be usable by players
     */
    public function clearRegistry()
    {
        $this->m_WindowRegistry = [];
    }

    public function onDataPacket(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        if ($packet instanceof ModalFormResponsePacket)
        {
            $l_PlayerIndex = $event->getPlayer()->getUniqueId()->toBinary();
            if (isset($this->m_WindowRegistry[$l_PlayerIndex]))
            {
                $l_ResponseData = json_decode($packet->formData, true);
                if (is_null($l_ResponseData))
                    return;

                $l_PlayerWindow = $this->m_WindowRegistry[$l_PlayerIndex];
                if ($l_PlayerWindow instanceof Window)
                {
                    if (!$l_PlayerWindow->handleResponse($l_ResponseData))
                        $l_PlayerWindow->open();
                }
            }
        }
    }
}
