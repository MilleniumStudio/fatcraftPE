<?php

declare(strict_types = 1);

namespace fatutils\ui\windows\parts;

//[
//    "type" => "label",
//    "text" => "Label\n§4red : test second line"
//],

class Label extends UiPart implements FormWindowCompatible
{
    public function __construct()
    {
        $this->getData()["type"] = "label";
    }

    public function setText(string $p_Text):Label
    {
        $this->getData()["text"] = $p_Text;
        return $this;
    }
}
