<?php

declare(strict_types = 1);

namespace fatutils\ui\windows\parts;

//[
//    "text" => "text 1",
//    "image" => [
//        "type" => "url",
//        "data" => "https://maxcdn.icons8.com/Share/icon/DIY//paint_brush1600.png"
//    ]
//],

class Button extends UiPart implements ButtonWindowCompatible
{
    private $m_Callback;

    public function setText(string $p_Text):Button
    {
        $this->getData()["text"] = $p_Text;
        return $this;
    }

    public function setImage(string $p_Url):Button
    {
        $this->getData()["image"] = [];
        $this->getData()["image"]["type"] = "url";
        $this->getData()["image"]["data"] = $p_Url;
        return $this;
    }

    public function setCallback(Callable $p_Callback):Button
    {
        $this->m_Callback = $p_Callback;
        return $this;
    }

    public function getCallback():Callable
    {
        return $this->m_Callback;
    }
}
