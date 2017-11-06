<?php

namespace fatutils\signs;

use pocketmine\tile\Sign;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use fatutils\FatUtils;

class CustomSign
{
    public $name;
    public $sign;
    public $update = false;
    public $function = null;
    public $text = array("", "", "", "");
    public $data = array();
    public $commands = array();

    public function __construct($name, &$sign = null)
    {
        $this->name = $name;
        $this->sign = $sign;
    }

    public function onTick(int $currentTick)
    {
        if ($this->update)
        {
            if ($this->function !== null)
            {
                $this->function->onTick($currentTick);
            }
        }
    }

    public function updateTexte()
    {
        if ($this->sign instanceof Sign)
        {
            $this->sign->setText($this->text[0], $this->text[1], $this->text[2], $this->text[3]);
        }
    }

    public function onInterract(Player $player, int $p_Index = -1)
    {
        if ($this->function !== null)
        {
            $this->function->onInterract($player, $p_Index);
        }
        foreach ($this->commands as $cmd)
        {
            FatUtils::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $cmd));
        }
    }
}
