<?php

namespace fatutils\holograms;

use pocketmine\math\Vector3;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 13/09/2017
 * Time: 12:00
 */
class Hologram
{

    /** @var FloatingTextParticle $floatingText */
    public $particle;
    public $level;

    public function __construct(Vector3 $pos, level $level, $title = "", $text)
    {
        $this->particle = new FloatingTextParticle($pos, $text, $title);
        $this->level = $level;
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
