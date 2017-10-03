<?php

declare(strict_types = 1);

namespace fatutils\ui\windows;

use fatutils\ui\windows\Window;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class ButtonMenuWindow extends Window
{

    public function process(): void
    {
        $this->data = [
            "type" => "form",
            "title" => "menu title",
            "content" => "text content",
            "buttons" => [
                [
                    "text" => "text 1",
                    "image" => [
                        "type" => "url",
                        "data" => "https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png"
                    ]
                ],
                [
                    "text" => "text 2",
                    "image" => [
                        "type" => "url",
                        "data" => "http://www.sidecarpost.com/wp-content/uploads/2014/03/Icon-BaselinePreset-100x100.png"
                    ]
                ],
                [
                    "text" => "text 3",
                    "image" => [
                        "type" => "url",
                        "data" => "http://icons.iconarchive.com/icons/dtafalonso/android-l/512/Settings-L-icon.png"
                    ]
                ],
                [
                    "text" => "Coming soon..."
                ],
                [
                    "text" => "test 4",
                    "image" => [
                        "type" => "url",
                        "data" => "http://www.pngmart.com/files/3/Red-Cross-Transparent-PNG.png"
                    ]
                ]
            ]
        ];
    }

    public function handle(ModalFormResponsePacket $packet): bool
    {
        $data = json_decode($packet->formData, true);
        var_dump($data);
        return false;
    }

}
