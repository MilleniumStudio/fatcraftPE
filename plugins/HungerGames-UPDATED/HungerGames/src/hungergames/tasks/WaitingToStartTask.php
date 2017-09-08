<?php
namespace hungergames\tasks;
use hungergames\lib\utils\Msg;
use hungergames\Loader;
use hungergames\obj\HungerGames;
use pocketmine\block\Block;
use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Chest;
class WaitingToStartTask extends PluginTask{
    /** @var Loader */
    private $HGApi;
    /** @var HungerGames */
    private $game;
    /** @var float */
    private $seconds;
    public function __construct(Loader $main, HungerGames $game){
        parent::__construct($main);
        $this->HGApi = $main;
        $this->game = $game;
        $this->seconds = $game->getWaitingSeconds();
    }
    /**
     * @param $tick
     */
    public function onRun($tick){
        $count = $this->HGApi->getStorage()->getAllWaitingPlayersInGameCount($this->game);
        --$this->seconds;
        if ($count == 0) {
            $this->HGApi->getGlobalManager()->getGameManager($this->game)->setStatus("open");
            $this->HGApi->getServer()->getScheduler()->cancelTask($this->getTaskId());
            $this->HGApi->getGlobalManager()->getGameManager($this->game)->refresh();
            return;
        }
        if($this->seconds > 0) {
            if ($count >= $this->game->getMinimumPlayers()) {
                $message = str_replace(["%seconds%", "%game%"], [$this->seconds, $this->game->getName()], Msg::getHGMessage("hg.message.waiting"));
                $this->HGApi->getGlobalManager()->getGameManagerByName($this->game->getName())->sendGamePopup(Msg::color($message));
                foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
                    if (!$script->isEnabled()) continue;
                    $script->whileWaitingToStart($this->HGApi->getStorage()->getPlayersInGame($this->game), $this->game);
                }
                return;
            }
            if($count < $this->game->getMinimumPlayers()){
                $this->HGApi->getGlobalManager()->getGameManager($this->game)->setStatus("waiting");
                $this->HGApi->getServer()->getScheduler()->cancelTask($this->getTaskId());
                $task = new WaitingForPlayersTask($this->HGApi, $this->game);
                $h = $this->HGApi->getServer()->getScheduler()->scheduleRepeatingTask($task, 20);
                $task->setHandler($h);
                return;
            }
            return;
        }
        if($this->seconds == 0 and $count >= $this->game->getMinimumPlayers()){
            if($this->game->isSkyWars() === "no") {
                $task = new GameRunningTask($this->HGApi, $this->game);
                $h = $this->HGApi->getServer()->getScheduler()->scheduleRepeatingTask($task, 20);
                $task->setHandler($h);
            }else{
                foreach($this->HGApi->getStorage()->getAllWaitingPlayers() as $p){
                    $p->getLevel()->setBlock($p->subtract(0, 1), Block::get(0));
                }
            }
            $this->HGApi->getGlobalManager()->getGameManager($this->game)->setStatus("running");
            foreach($this->game->gameLevel->getTiles() as $tile){
                if($tile instanceof Chest){
                    $tile->getInventory()->setContents($this->game->getChestItems());
                }
            }
            foreach($this->HGApi->getStorage()->getAllWaitingPlayers() as $p){
                $this->HGApi->getStorage()->addPlayer($p, $this->game);
            }
            $this->HGApi->getServer()->getScheduler()->cancelTask($this->getTaskId());
            $message = str_replace("%game%", $this->game->getName(), Msg::getHGMessage("hg.message.start"));
            $this->HGApi->getGlobalManager()->getGameManagerByName($this->game->getName())->sendGameMessage(Msg::color($message));
            $this->HGApi->getStorage()->removePlayersInWaitingGame($this->game);
            foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
                if (!$script->isEnabled()) return;
                $script->onGameStart($this->HGApi->getStorage()->getPlayersInGame($this->game), $this->game);
            }
            return;
        }
    }
}