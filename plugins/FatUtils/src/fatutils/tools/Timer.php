<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 11:53
 */

namespace fatutils\tools;

use fatutils\FatUtils;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

class Timer
{
	private $m_Timeout;

	private $m_StartCallback;
	private $m_StopCallback;

	/**
	 * Timer constructor.
	 * @param int $p_Delay
	 * @param int $p_Timeout
	 */
	public function __construct(int $p_Timeout)
	{
		$this->m_Timeout = $p_Timeout;
	}

	public function addStartCallback(Callable $p_StartCallback):Timer
	{
		$this->m_StartCallback = $p_StartCallback;
		return $this;
	}

	public function addStopCallback(Callable $p_StopCallback):Timer
	{
		$this->m_StopCallback = $p_StopCallback;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getTimeout(): int
	{
		return $this->m_Timeout;
	}

	/**
	 * @return mixed
	 */
	public function getStartCallback()
	{
		return $this->m_StartCallback;
	}

	/**
	 * @return mixed
	 */
	public function getStopCallback()
	{
		return $this->m_StopCallback;
	}

	public function start()
	{
		FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new class(FatUtils::getInstance(), $this) extends PluginTask
		{
			private $m_TimerInstance = null;
			private $m_TickLived = 0;

			/**
			 *  constructor.
			 * @param Plugin $p_Owner
			 * @param Timer $p_Instance
			 */
			public function __construct(Plugin $p_Owner, Timer $p_Instance)
			{
				parent::__construct($p_Owner);
				$this->m_TimerInstance = $p_Instance;
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
				$this->m_TickLived++;
				if ($this->m_TickLived > $this->m_TimerInstance->getTimeout())
				{
					call_user_func($this->m_TimerInstance->getStopCallback());
					FatUtils::getInstance()->getServer()->getScheduler()->cancelTask($this->getTaskId());
				}
			}
		}, 1);
	}
}