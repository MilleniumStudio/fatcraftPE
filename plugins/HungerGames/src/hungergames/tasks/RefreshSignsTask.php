<?php
namespace hungergames\tasks;
use hungergames\Loader;
use pocketmine\scheduler\PluginTask;
class RefreshSignsTask extends PluginTask{
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
        $this->HGApi->getSignManager()->refreshAllSigns();
    }
}