<?php

declare(strict_types = 1);

namespace fatutils\ui\windows;

use fatutils\tools\TextFormatter;
use pocketmine\Player;

class ModalWindow extends Window
{
    private $m_ValidationCallable = null;
    private $m_CancellationCallable = null;

    public function __construct(Player $player)
    {
        parent::__construct($player);
        $this->setType("modal");
        $this->setContent("");
    }

    public function setContent(string $p_Content): ModalWindow
    {
        $this->m_Data["content"] = $p_Content;
        return $this;
    }

    public function setValidationButton(Callable $p_Callback, string $p_Title = null): ModalWindow
    {
        if (is_null($p_Title))
            $p_Title = (new TextFormatter("window.yes"))->asStringForPlayer($this->getPlayer());

        $this->m_Data["button1"] = $p_Title;
        $this->m_ValidationCallable = $p_Callback;
        return $this;
    }

    public function setCancellationButton(Callable $p_Callback, string $p_Title = null): ModalWindow
    {
        if (is_null($p_Title))
            $p_Title = (new TextFormatter("window.no"))->asStringForPlayer($this->getPlayer());

        $this->m_Data["button2"] = $p_Title;
        $this->m_CancellationCallable = $p_Callback;
        return $this;
    }

    public static function openTestWindow(Player $p_Player): void
    {
        $l_Window = new ModalWindow($p_Player);
        $l_Window->setTitle("ModalWindow");
        $l_Window->setValidationButton(function () {
            echo "ModalWindow Validation";
        });
        $l_Window->setCancellationButton(function () {
            echo "ModalWindow Cancellation";
        });
        $l_Window->open();
    }

    public function getValidationCallable(): ?Callable
    {
        return $this->m_ValidationCallable;
    }

    public function getCancellationCallable(): ?Callable
    {
        return $this->m_CancellationCallable;
    }

    public function handleResponse($p_Data): bool
    {
        if (is_bool($p_Data))
        {
            if ($p_Data && is_callable($this->getValidationCallable()))
                $this->getValidationCallable()();
            else if (is_callable($this->getCancellationCallable()))
                $this->getCancellationCallable()();
        }
        return true;
    }

    protected function getWindowId(): int
    {
        return 2;
    }
}
