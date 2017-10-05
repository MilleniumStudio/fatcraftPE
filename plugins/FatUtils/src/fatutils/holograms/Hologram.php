<?php

namespace fatutils\holograms;

use pocketmine\math\Vector3;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Location;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Hologram
{

    public $name;
    public $particle;
    public $level;

    public function __construct(string $p_Name, Location $pos, $text, $title)
    {
        $this->name = $p_Name;
        $this->particle = new FloatingTextParticle(new Vector3($pos->x, $pos->y, $pos->z), $text, $title);
        $this->level = $pos->level;
        $this->send();
    }

    public function updatePosition(Vector3 $pos)
    {
        $this->particle->setComponents($pos->x, $pos->y, $pos->z);
        $this->send();
    }

    public function updateTitle(string $title)
    {
        $this->particle->setTitle($title);
        $this->send();
    }

    public function updateText(array $texts)
    {
        $this->particle->setText(TextFormat::RESET . implode(TextFormat::RESET . "\n", $texts));
        $this->send();
    }

    public function send()
    {
        if ($this->level != null)
        {
            $this->level->addParticle($this->particle, Server::getInstance()->getOnlinePlayers());
        }
        else
        {
            echo "Level is null !";
        }
    }

}
