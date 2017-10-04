<?php

declare(strict_types = 1);

namespace fatutils\ui\windows\parts;

//[
//    "type" => "dropdown",
//    "text" => "Dropdown",
//    "options" => ["option 1", "option 2", "option 3"],
//    "default" => 0,
//    "enabled" => 1
//],

class Dropdown extends UiPart implements FormWindowCompatible
{
    public function __construct()
    {
        $this->getData()["type"] = "dropdown";
    }

    public function setText(string $p_Text):Dropdown
    {
        $this->getData()["text"] = $p_Text;
        return $this;
    }

    public function setOptions(array $p_Options):Dropdown
    {
        $this->getData()["options"] = $p_Options;
        return $this;
    }

    public function setDefault(int $p_DefaultOptionsIndex):Dropdown
    {
        $this->getData()["default"] = $p_DefaultOptionsIndex;
        return $this;
    }

//    public function setEnabled(bool $p_Enabled):Dropdown
//    {
//        $this->getData()["enabled"] = $p_Enabled;
//        return $this;
//    }
}
