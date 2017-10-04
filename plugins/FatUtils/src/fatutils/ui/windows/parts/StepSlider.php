<?php

declare(strict_types = 1);

namespace fatutils\ui\windows\parts;

//[
//    "type" => "step_slider",
//    "text" => "Step slider",
//    "steps" => ["§4red", "green", "yellow", "blue", "black", "white"]
//],

class StepSlider extends UiPart implements FormWindowCompatible
{
    public function __construct()
    {
        $this->m_Data["type"] = "step_slider";
    }

    public function setText(string $p_Text):StepSlider
    {
        $this->m_Data["text"] = $p_Text;
        return $this;
    }

    public function setSteps(array $p_Steps):StepSlider
    {
        $this->m_Data["steps"] = $p_Steps;
        return $this;
    }
}
