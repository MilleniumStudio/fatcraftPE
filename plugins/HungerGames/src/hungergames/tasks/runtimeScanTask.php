<?php
namespace hungergames\tasks;
use hungergames\lib\utils\exc;
use hungergames\Loader;
use pocketmine\scheduler\PluginTask;
class runtimeScanTask extends PluginTask{
    /**
     * Actions to execute when run
     *
     * @param $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick){
        if(Loader::getInstance()->getStorage()->scanOverload()){
            Loader::getInstance()->getLogger()->emergency(exc::_("%%cPlease restart server to prevent it from overloading.%%cGame load has reached max players size (600 players)."));
        }
    }
}