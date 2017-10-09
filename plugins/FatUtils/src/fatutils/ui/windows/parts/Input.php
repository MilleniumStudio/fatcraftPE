<?php

declare(strict_types = 1);

namespace fatutils\ui\windows\parts;

//[
//    "type" => "input",
//    "text" => "Text input",
//    "placeholder" => "placeholder text"
//    "default" => "default text"
//],

class Input extends UiPart implements FormWindowCompatible
{
    public function __construct()
    {
        $this->m_Data["type"] = "input";
    }

    public function setText(string $p_Text):Input
    {
        $this->m_Data["text"] = $p_Text;
        return $this;
    }

    public function setPlaceholder(string $p_PlaceholderText):Input
    {
        $this->m_Data["placeholder"] = $p_PlaceholderText;
        return $this;
    }

    public function setDefault(string $p_DefaultText):Input
    {
        $this->m_Data["default"] = $p_DefaultText;
        return $this;
    }
}
