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
                    "text" => "Text input",
                    "placeholder" => "placeholder text"
                ],
                [
                    "type" => "input",
                    "text" => "Text input",
                    "default" => "default text"
                ],
                [
                    "type" => "label",
                    "text" => "Label\n§4red : test second line"
                ],
                [
                    "type" => "slider",
                    "text" => "Slider",
                    "min" => 0,
                    "max" => 10,
                    "step" => 1,
                    "default" => 4
                ],
                [
                    "type" => "step_slider",
                    "text" => "Step slider",
                    "steps" => ["§4red", "green", "yellow", "blue", "black", "white"]
                ],
                [
                    "type" => "dropdown",
                    "text" => "Dropdown",
                    "options" => ["option 1", "option 2", "option 3"],
                    "default" => 0,
                    "enabled" => 1
                ],
                [
                    "type" => "toggle",
                    "text" => "Toggle",
                    "default" => true
                ]
            ]
        ];
    }

    public function handle(ModalFormResponsePacket $packet): bool
    {
        $data = json_decode($packet->formData, true);
        var_dump($data);
        // $data is an array
//        array(8) {
//            [0]=>
//            string(0) ""
//            [1]=>
//            string(12) "default text"
//            [2]=>
//            NULL
//            [3]=>
//            int(4)
//            [4]=>
//            int(0)
//            [5]=>
//            int(0)
//            [6]=>
//            int(0)
//            [7]=>
//            bool(true)
//        }

        return true;
    }

}
