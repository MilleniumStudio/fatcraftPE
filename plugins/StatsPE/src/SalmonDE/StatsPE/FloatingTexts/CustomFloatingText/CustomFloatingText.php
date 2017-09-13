<?php
namespace SalmonDE\StatsPE\FloatingTexts\CustomFloatingText;
use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 13/09/2017
 * Time: 12:00
 */
abstract class CustomFloatingText
{
    /** @var FloatingTextParticle $floatingText */
    public $floatingText;
    public $level;

    public function __construct(\pocketmine\math\Vector3 $pos, level $level, $text, $title = "")
    {
        $this->floatingText = new FloatingTextParticle($pos, $text, $title);
        CustomFloatingTextManager::add($this);
        $this->level = $level;
        $this->send();
    }

    public function setTitle(string $title)
    {
        $this->floatingText->setTitle($title);
        $this->send();
    }

    public function setText(array $texts){
        $this->floatingText->setText(TextFormat::RESET.implode(TextFormat::RESET."\n", $texts));
        $this->send();
    }

    public function send()
    {
        if($this->level != null)
            $this->level->addParticle($this->floatingText, Server::getInstance()->getOnlinePlayers());
        else
            echo "Level is null !";
    }

    public abstract function needUpdate(int $tick) : bool;
    public abstract function update();
}