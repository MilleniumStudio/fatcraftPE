<?php

namespace fatutils\holograms;

use pocketmine\math\Vector3;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Location;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use fatutils\tools\TextFormatter;

class Hologram
{

    public $name;
    public $rawTitle;
    public $rawText;
    public $particle;
    public $pos;
    public $level;

    public function __construct(string $p_Name, Location $pos, $title, $text)
    {
        $this->name = $p_Name;
        $this->rawTitle = $title;
        $this->rawText = $text;
        $this->pos = $pos;
        $this->particle = new FloatingTextParticle($pos, $this->rawTitle, $this->rawText);
        $this->level = $pos->level;
//        $this->send();
    }

    public function updatePosition(Vector3 $pos)
    {
        $this->particle->setComponents($pos->x, $pos->y, $pos->z);
//        $this->send();
    }

    public function updateTitle(string $title)
    {
        $this->particle->setTitle($title);
//        $this->send();
    }

    public function updateText(array $texts)
    {
        $this->particle->setText(TextFormat::RESET . implode(TextFormat::RESET . "\n", $texts));
//        $this->send();
    }

    public function sendRaw()
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

    public function sendToPlayer(Player $player, array $p_Params = [])
    {
        $p_Params['playername'] = $player->getName();
        if ($this->level != null)
        {
            $this->particle->setTitle((new TextFormatter($this->rawTitle, $p_Params))->asStringForPlayer($player));
            $this->particle->setText((new TextFormatter($this->rawText, $p_Params))->asStringForPlayer($player));
            $this->level->addParticle($this->particle, [$player]);
        }
        else
        {
            echo "Level is null !";
        }
    }

}
