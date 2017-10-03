<?php

declare(strict_types = 1);

namespace fatutils\ui\windows;

use fatutils\ui\windows\Window;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class InputMenuWindow extends Window
{

    public function process(): void
    {
        $this->data = [
            "type" => "custom_form",
            "title" => "menu title",
            "content" => [
                [
                    "type" => "input",
                    "text" => "default text",
                    "default" => "default text",
                    "placeholder" => "placeholder text"
                ],
                [
                    "type" => "slider",
                    "text" => "default text",
                    "min" => 0,
                    "max" => 10,
                    "step" => 1,
                    "default" => 4
                ],
                [
                    "type" => "dropdown",
                    "text" => "default text",
                    "default" => 0,
                    "options" => ["option 1", "option 2", "option 3"]
                ],
                [
                    "type" => "toggle",
                    "text" => "toggle test",
                    "default" => true
                ]
            ]
        ];
    }

    public function handle(ModalFormResponsePacket $packet): bool
    {
        $data = json_decode($packet->formData, true);
        var_dump($data);
        return true;
    }

}
