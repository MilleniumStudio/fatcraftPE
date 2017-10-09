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
        $this->m_Data["text"] = $p_Text;
        return $this;
    }

    public function setImage(string $p_Url):Button
    {
        $this->m_Data["image"] = [];
        $this->m_Data["image"]["type"] = "url";
        $this->m_Data["image"]["data"] = $p_Url;
        return $this;
    }

    /**
     * @param callable(void) $p_Callback
     * @return Button
     */
    public function setCallback(Callable $p_Callback):Button
    {
        $this->m_Callback = $p_Callback;
        return $this;
    }

    public function getCallback(): ?Callable
    {
        return $this->m_Callback;
    }
}
