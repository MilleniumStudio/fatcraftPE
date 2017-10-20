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
use pocketmine\scheduler\TaskHandler;

class LoopedExec
{
	private $m_Task = null;

	public function __construct(callable $p_Callback, int $p_TickPeriod = 1)
	{
		$this->m_Task = FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new class(FatUtils::getInstance(), $p_Callback) extends PluginTask {
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
		}, $p_TickPeriod);
	}

	public function cancel()
	{
		if ($this->m_Task instanceof TaskHandler)
		{
			$this->m_Task->cancel();
			$this->m_Task = null;
		}
	}
}