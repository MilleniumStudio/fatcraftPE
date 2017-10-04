<?php

declare(strict_types = 1);

namespace fatutils\ui\windows\parts;

abstract class UiPart
{
    protected $m_Data = [];

    public function getData(): array
    {
        return $this->m_Data;
    }
}
