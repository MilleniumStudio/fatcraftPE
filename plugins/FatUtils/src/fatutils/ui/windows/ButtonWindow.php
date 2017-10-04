<?php

declare(strict_types = 1);

namespace fatutils\ui\windows;

use fatutils\ui\windows\parts\Button;
use fatutils\ui\windows\parts\ButtonWindowCompatible;
use fatutils\ui\windows\parts\Input;
use fatutils\ui\windows\parts\UiPart;

use pocketmine\Player;

class ButtonWindow extends Window
{
    public function __construct(Player $player)
    {
        parent::__construct($player);
        $this->setType("form");
    }

    public function addPart(ButtonWindowCompatible $p_Part): ButtonWindow
    {
        if ($p_Part instanceof UiPart)
            $this->_addPart($p_Part);
        return $this;
    }

    public function setContent(string $p_Content): ButtonWindow
    {
        $this->m_Data["content"] = $p_Content;
        return $this;
    }

    public function getAsJson(): string
    {
        $this->m_Data["buttons"] = [];

        foreach ($this->getParts() as $l_Part)
        {
            if ($l_Part instanceof UiPart)
                $this->m_Data["buttons"][] = $l_Part->getData();
        }

        return parent::getAsJson();
    }

    public static function openTestWindow(Player $p_Player): void
    {
        $l_Window = new ButtonWindow($p_Player);
        $l_Window->setTitle("ButtonWindow");
        $l_Window->setContent("text content\ntest second line\ntest 3 lines");
        $l_Window->addPart((new Button())
            ->setText("text 1")
            ->setImage("https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png")
            ->setCallback(function() {
                echo "Button1\n";
            })
        );
        $l_Window->addPart((new Button())
            ->setText("text 2")
            ->setImage("http://www.sidecarpost.com/wp-content/uploads/2014/03/Icon-BaselinePreset-100x100.png")
            ->setCallback(function() {
                echo "Button2\n";
            })
        );
        $l_Window->addPart((new Button())
            ->setText("text 3")
            ->setImage("http://icons.iconarchive.com/icons/dtafalonso/android-l/512/Settings-L-icon.png")
            ->setCallback(function() {
                echo "Button3\n";
            })
        );
        $l_Window->addPart((new Button())
            ->setText("Coming soon...")
            ->setCallback(function() {
                echo "Button sans image\n";
            })
        );
        $l_Window->addPart((new Button())
            ->setText("text 4")
            ->setImage("http://www.pngmart.com/files/3/Red-Cross-Transparent-PNG.png")
            ->setCallback(function() use ($l_Window) {
                echo "Button4\n";
                $l_Window->open();
            })
        );
        $l_Window->open();

//        $this->m_Data = [
//            "type" => "form",
//            "title" => "menu title",
//            "content" => "text content\ntest second line\ntest 3 lines",
//            "buttons" => [
//                [
//                    "text" => "text 1",
//                    "image" => [
//                        "type" => "url",
//                        "data" => "https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png"
//                    ]
//                ],
//                [
//                    "text" => "text 2",
//                    "image" => [
//                        "type" => "url",
//                        "data" => "http://www.sidecarpost.com/wp-content/uploads/2014/03/Icon-BaselinePreset-100x100.png"
//                    ]
//                ],
//                [
//                    "text" => "text 3",
//                    "image" => [
//                        "type" => "url",
//                        "data" => "http://icons.iconarchive.com/icons/dtafalonso/android-l/512/Settings-L-icon.png"
//                    ]
//                ],
//                [
//                    "text" => "Coming soon..."
//                ],
//                [
//                    "text" => "test 4",
//                    "image" => [
//                        "type" => "url",
//                        "data" => "http://www.pngmart.com/files/3/Red-Cross-Transparent-PNG.png"
//                    ]
//                ]
//            ]
//        ];
    }

    public function handleResponse($p_Data): bool
    {
        if (is_int($p_Data))
        {
            $l_Part = $this->getParts()[$p_Data];
            if ($l_Part instanceof Button && is_callable($l_Part->getCallback()))
                $l_Part->getCallback()();
        }
        return true;
    }


    protected function getWindowId(): int
    {
        return 0;
    }
}
