<?php

declare(strict_types = 1);

namespace fatutils\ui\windows;

use fatutils\FatUtils;
use fatutils\ui\WindowsManager;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;

abstract class Window
{

    /** @var Loader */
    protected $m_Plugin = null;

    /** @var Player */
    protected $player = null;

    /** @var array */
    protected $data = [];

    public function __construct(FatUtils $p_Plugin, Player $player)
    {
        $this->m_Plugin = $p_Plugin;
        $this->player = $player;
        $this->process();
    }

    /**
     * @return string
     */
    public function getJson(): string
    {
        return json_encode($this->data);
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): FatUtils
    {
        return $this->m_Plugin;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param int           $menu
     * @param Player        $player
     * @param WindowHandler $windowHandler
     */
    public function navigate(int $menu, Player $player, WindowsManager $windowHandler): void
    {
        $packet = new ModalFormRequestPacket();
        $packet->formId = $windowHandler->getWindowIdFor($menu);
        $packet->formData = $windowHandler->getWindowJson($menu, $this->m_Plugin, $player);
        $player->dataPacket($packet);
    }

    protected abstract function process(): void;

    public abstract function handle(ModalFormResponsePacket $packet): bool;
}
