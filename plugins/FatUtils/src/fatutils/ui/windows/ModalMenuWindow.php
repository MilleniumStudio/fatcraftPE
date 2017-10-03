<?php

declare(strict_types = 1);

namespace fatutils\ui\windows;

use fatutils\ui\windows\Window;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class ModalMenuWindow extends Window
{

    public function process(): void
    {
        $this->data = [
            "type" => "modal",
            "title" => "menu title",
            "content" => "This is modal.",
            "button1" => "True Button",
            "button2" => "False Button"
        ];
    }

    public function handle(ModalFormResponsePacket $packet): bool
    {
        $data = json_decode($packet->formData, true);
        var_dump($data);
        // $data is true(button1) or false(button2)
        return true;
    }

}
