<?php
namespace hungergames\tasks;
use hungergames\Loader;
use pocketmine\scheduler\PluginTask;
class LoadGamesTask extends PluginTask{
    /** @var Loader */
    private $HGApi;
    public function __construct(Loader $main){
        parent::__construct($main);
        $this->HGApi = $main;
    }
    /**
     * @param $currentTick
     */
    public function onRun(int $currentTick){
        foreach($this->HGApi->getAllGameResources() as $game){
            $this->HGApi->getGlobalManager()->load($game);
        }
        $this->HGApi->getLogger()->info("All games have been loaded! At least that's what I think :p");
    }
}