<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 15:49
 */

namespace fatutils\tools;


use fatutils\FatUtils;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;

class DelayedExec
{

    /**
     * DelayedExec constructor.
     * @param int $p_Delay
     * @param callable $p_Callback
     */
    public function __construct(int $p_Delay, callable $p_Callback)
    {
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleDelayedTask(new class(FatUtils::getInstance(), $p_Callback) extends PluginTask {
            private $m_Callback;

            public function __construct(PluginBase $p_Plugin, callable $p_Callback)
            {
                parent::__construct($p_Plugin);
                $this->m_Callback = $p_Callback;
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
                $call = $this->m_Callback;
                if (is_callable($call))
                    $call();
            }
        }, $p_Delay);
    }
}