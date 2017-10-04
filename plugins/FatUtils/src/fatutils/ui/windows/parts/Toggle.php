<?php

declare(strict_types = 1);

namespace fatutils\ui\windows\parts;

//[
//    "type" => "toggle",
//    "text" => "Toggle",
//    "default" => true
//]

class Toggle extends UiPart implements FormWindowCompatible
{
    public function __construct()
    {
        $this->getData()["type"] = "step_slider";
    }

    public function setText(string $p_Text):Toggle
    {
        $this->getData()["text"] = $p_Text;
        return $this;
    }

    public function setDefault(bool $p_DefaultValue):Toggle
    {
        $this->getData()["default"] = $p_DefaultValue;
        return $this;
    }
}
