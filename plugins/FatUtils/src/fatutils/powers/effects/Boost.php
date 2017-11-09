<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 07/11/2017
 * Time: 12:00
 */

namespace fatutils\powers\effects;

use fatutils\FatUtils;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\TaskHandler;

class Boost extends \fatutils\powers\APower
{

    function getIcon(): \pocketmine\item\Item
    {
        return \pocketmine\item\ItemFactory::get(40);
    }

    function action(): bool
    {
        $this->owner->sendMessage("L'Aigle de la route !"); //lol
        $this->destroy();

        $task = new BoostTicker(FatUtils::getInstance());
        $task->init($this->owner);
        $taskHandler = FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask($task, 1);
        $task->setReference($taskHandler); //ugly but no better idea
        return true;
    }
}

class BoostTicker extends PluginTask
{

    /** @var Player $owner */
    public $player;
    /** @var TaskHandler $myHandler */
    public $myHandler = null;
    public $timer = 20;
    public $maxSpeed = 2;
    public $minSpeed = 0.2;

    public function init(Player $player)
    {
        $this->player = $player;
    }

    public function setReference($taskHandler)
    {
        $this->myHandler = $taskHandler;
    }

    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        if ($this->timer-- > 0) {
//            echo $this->timer . "  (" . min(max($this->timer / 10, $this->minSpeed), $this->maxSpeed) . ")" . "\n";
            $x = cos(deg2rad($this->player->vehicle->yaw)) * min(max($this->timer / 10, $this->minSpeed), $this->maxSpeed);
            $z = sin(deg2rad($this->player->vehicle->yaw)) * min(max($this->timer / 10, $this->minSpeed), $this->maxSpeed);
            $this->player->vehicle->setMotion($this->player->vehicle->getMotion()->add(new Vector3($x, 0, $z)));
        } else {
            $this->myHandler->cancel();
        }
    }
}