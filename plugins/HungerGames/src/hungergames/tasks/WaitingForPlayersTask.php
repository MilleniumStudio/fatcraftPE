<?php
namespace hungergames\tasks;
use hungergames\lib\utils\Msg;
use hungergames\Loader;
use hungergames\obj\HungerGames;
use pocketmine\scheduler\PluginTask;
class WaitingForPlayersTask extends PluginTask{
    /** @var Loader */
    private $HGApi;
    /** @var HungerGames */
    private $game;

    public function __construct(Loader $main, HungerGames $game){
        parent::__construct($main);
        $this->HGApi = $main;
        $this->game = $game;
    }
    /**
     * @param $tick
     */
    public function onRun(int $tick){
        $count = $this->HGApi->getStorage()->getAllWaitingPlayersInGameCount($this->game);
        if ($count == 0) {
            $this->HGApi->getGlobalManager()->getGameManager($this->game)->setStatus("open");
            $this->HGApi->getServer()->getScheduler()->cancelTask($this->getTaskId());
            $this->HGApi->getGlobalManager()->getGameManager($this->game)->refresh();
            return;
        }
        if ($count < $this->game->getMinimumPlayers()) {
            foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
                if (!$script->isEnabled()) continue;
                $script->whileWaitingForPlayers($this->HGApi->getStorage()->getPlayersInGame($this->game), $this->game);
            }
            $msg = Msg::getHGMessage("hg.message.awaiting");
            $msg = str_replace("%game%", $this->game->getName(), $msg);
            $this->HGApi->getGlobalManager()->getGameManagerByName($this->game->getName())->sendGamePopup(Msg::color($msg));
            return;
        }
        if ($count >= $this->game->getMinimumPlayers()) {
            $this->HGApi->getGlobalManager()->getGameManager($this->game)->setStatus("waiting");
            $this->HGApi->getServer()->getScheduler()->cancelTask($this->getTaskId());
            $task = new WaitingToStartTask($this->HGApi, $this->game);
            $h = $this->HGApi->getServer()->getScheduler()->scheduleRepeatingTask($task, 20);
            $task->setHandler($h);
            return;
        }
    }
}