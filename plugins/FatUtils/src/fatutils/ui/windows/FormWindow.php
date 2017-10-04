<?php

declare(strict_types = 1);

namespace fatutils\ui\windows;

use fatutils\ui\windows\parts\Dropdown;
use fatutils\ui\windows\parts\Input;
use fatutils\ui\windows\parts\Label;
use fatutils\ui\windows\parts\Slider;
use fatutils\ui\windows\parts\StepSlider;
use fatutils\ui\windows\parts\Toggle;
use fatutils\ui\windows\parts\UiPart;

use fatutils\ui\windows\parts\FormWindowCompatible;
use pocketmine\Player;
use ReflectionObject;

class FormWindow extends Window
{
    private $m_Callback;

    public function __construct(Player $player)
    {
        parent::__construct($player);
        $this->setType("custom_form");
    }

    public function addPart(FormWindowCompatible $p_Part): FormWindow
    {
        if ($p_Part instanceof UiPart)
            $this->_addPart($p_Part);
        return $this;
    }

    public function setCallback(Callable $p_Callback):FormWindow
    {
        $this->m_Callback = $p_Callback;
        return $this;
    }

    public function getAsJson(): string
    {
        $this->m_Data["content"] = [];

        foreach ($this->getParts() as $l_Part)
        {
            if ($l_Part instanceof UiPart)
                $this->m_Data["content"][] = $l_Part->getData();
        }

        return parent::getAsJson();
    }

    public static function openTestWindow(Player $p_Player): void
    {
        $l_FormWindow = new FormWindow($p_Player);
        $l_FormWindow->setTitle("menu title");
        $l_FormWindow->addPart((new Input())
            ->setText("Text input")
            ->setPlaceholder("placeholder text")
        );
        $l_FormWindow->addPart((new Input())
            ->setText("Text input")
            ->setDefault("default text")
        );
        $l_FormWindow->addPart((new Label())
            ->setText("Label\n§4red : test second line")
        );
        $l_FormWindow->addPart((new Slider())
            ->setText("Slider")
            ->setMin(0)
            ->setMax(10)
            ->setStep(1)
            ->setDefault(4)
        );
        $l_FormWindow->addPart((new StepSlider())
            ->setText("Step slider")
            ->setSteps(["§4red", "green", "yellow", "blue", "black", "white"])
        );
        $l_FormWindow->addPart((new Dropdown())
            ->setText("Dropdown")
            ->setOptions(["option 1", "option 2", "option 3"])
            ->setDefault(0)
        );
        $l_FormWindow->addPart((new Toggle())
            ->setText("Toggle")
            ->setDefault(true)
        );
        $l_FormWindow->setCallback(function($p_Data) {
            var_dump($p_Data);
        });
        $l_FormWindow->open();

//        $this->m_Data = [
//            "type" => "custom_form",
//            "title" => "menu title",
//            "content" => [
//                [
//                    "type" => "input",
//                    "text" => "Text input",
//                    "placeholder" => "placeholder text"
//                ],
//                [
//                    "type" => "input",
//                    "text" => "Text input",
//                    "default" => "default text"
//                ],
//                [
//                    "type" => "label",
//                    "text" => "Label\n§4red : test second line"
//                ],
//                [
//                    "type" => "slider",
//                    "text" => "Slider",
//                    "min" => 0,
//                    "max" => 10,
//                    "step" => 1,
//                    "default" => 4
//                ],
//                [
//                    "type" => "step_slider",
//                    "text" => "Step slider",
//                    "steps" => ["§4red", "green", "yellow", "blue", "black", "white"]
//                ],
//                [
//                    "type" => "dropdown",
//                    "text" => "Dropdown",
//                    "options" => ["option 1", "option 2", "option 3"],
//                    "default" => 0,
//                    "enabled" => 1
//                ],
//                [
//                    "type" => "toggle",
//                    "text" => "Toggle",
//                    "default" => true
//                ]
//            ]
//        ];
    }

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

    public function handleResponse($p_Data): bool
    {
        if (is_callable($this->getCallback()))
        {
            $params = (new ReflectionObject((object)$this->getCallback()))->getMethod('__invoke')->getParameters();
            if (count($params) == 0)
                $this->getCallback()();
            if (count($params) == 1)
                $this->getCallback()($p_Data);
        }
        return true;
    }

    protected function getWindowId(): int
    {
        return 1;
    }

    public function getCallback():Callable
    {
        return $this->m_Callback;
    }
}
