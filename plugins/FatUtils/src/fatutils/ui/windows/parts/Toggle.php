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
        $this->m_Data["type"] = "toggle";
    }

    public function setText(string $p_Text):Toggle
    {
        $this->m_Data["text"] = $p_Text;
        return $this;
    }

    public function setDefault(bool $p_DefaultValue):Toggle
    {
        $this->m_Data["default"] = $p_DefaultValue;
        return $this;
    }
}
