<?php

declare(strict_types = 1);

namespace fatutils\ui\windows\parts;

//[
//    "type" => "slider",
//    "text" => "Slider",
//    "min" => 0,
//    "max" => 10,
//    "step" => 1,
//    "default" => 4
//],

class Slider extends UiPart implements FormWindowCompatible
{
    public function __construct()
    {
        $this->m_Data["type"] = "slider";
    }

    public function setText(string $p_Text):Slider
    {
        $this->m_Data["text"] = $p_Text;
        return $this;
    }

    public function setMin(int $p_Min):Slider
    {
        $this->m_Data["min"] = $p_Min;
        return $this;
    }

    public function setMax(int $p_Max):Slider
    {
        $this->m_Data["max"] = $p_Max;
        return $this;
    }

    public function setStep(int $p_Step):Slider
    {
        $this->m_Data["step"] = $p_Step;
        return $this;
    }

    public function setDefault(int $p_Default):Slider
    {
        $this->m_Data["default"] = $p_Default;
        return $this;
    }
}
