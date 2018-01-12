<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 11:53
 */

namespace fatutils\tools\schedulers;

use fatutils\FatUtils;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\TaskHandler;

class Timer
{
	private $m_OriginTimeout = 0;
    private $m_TickDelay = 0;
	private $m_TickLeft = 0;

	private $m_Paused = false;

	private $m_StartCallback;
	private $m_StopCallback;
	private $m_TickCallback;
	private $m_SecondCallback;

	private $m_Task = null;

	private $m_isRuning = false;

	/**
	 * Timer constructor.
	 * @param int $p_Timeout
	 */
	public function __construct(int $p_Timeout)
	{
		$this->m_OriginTimeout = $p_Timeout;
        $this->m_TickLeft = $p_Timeout;
	}

    public function addTickDelay(int $p_Delay):Timer
    {
        $this->m_TickDelay = $p_Delay;
        return $this;
    }

	public function addStartCallback(Callable $p_StartCallback):Timer
	{
		$this->m_StartCallback[] = $p_StartCallback;
		return $this;
	}

	public function addStopCallback(Callable $p_StopCallback):Timer
	{
		$this->m_StopCallback[] = $p_StopCallback;
		return $this;
	}

    public function addTickCallback(Callable $p_TickCallback):Timer
    {
        $this->m_TickCallback[] = $p_TickCallback;
        return $this;
    }

	public function addSecondCallback(Callable $p_SecondCallback):Timer
	{
		$this->m_SecondCallback[] = $p_SecondCallback;
		return $this;
	}

	public function getElapsedTimeInTick():int
    {
        return $this->m_OriginTimeout - $this->m_TickLeft;
    }

    public function getTimeSpentRatio():float
    {
        return (1.0 - ((((float) ($this->getElapsedTimeInTick())) / (float) $this->m_OriginTimeout)));
    }

	/**
	 * @return int
	 */
	public function getTickLeft(): int
	{
		return $this->m_TickLeft;
	}

    public function getSecondLeft(): int
    {
        return $this->m_TickLeft / 20;
    }

    public function getDelayLeft(): int
    {
        return $this->m_TickDelay;
    }

    /* PLEASE DON'T USE THAT, it's intended for package scope */
    function _modTime(int $p_Modifier)
    {
        $this->m_TickLeft += $p_Modifier;
    }

    /* PLEASE DON'T USE THAT, it's intended for package scope */
    function _modDelay(int $p_Modifier)
    {
        $this->m_TickDelay += $p_Modifier;
    }

    public function addTime(int $p_Modifier)
    {
        $this->m_TickLeft += $p_Modifier;
        $this->m_OriginTimeout += $p_Modifier;
    }

	/**
	 * @return mixed
	 */
	public function getStartCallback()
	{
		return $this->m_StartCallback;
	}

    public function getTickCallback()
    {
        return $this->m_TickCallback;
    }

	public function getSecondCallback()
	{
		return $this->m_SecondCallback;
	}

	/**
	 * @return mixed
	 */
	public function getStopCallback()
	{
		return $this->m_StopCallback;
	}

	public function isPaused():bool
	{
		return $this->m_Paused;
	}

	public function pause()
	{
		$this->m_Paused = true;
		$this->m_isRuning = false;
	}

	public function cancel()
    {
        if ($this->m_Task instanceof TaskHandler)
		{
            $this->m_Task->cancel();
			$this->m_Task = null;
			$this->m_isRuning = false;
		}
    }

	public function isRunning()
	{
		return $this->m_isRuning;
	}

	public function start():Timer
	{
		if ($this->m_isRuning == true)
			return $this;
		$this->m_isRuning = true;
		if (!is_null($this->m_Task) && $this->isPaused())
			$this->m_Paused = false;
		else
		{
			$this->m_Task = FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new class(FatUtils::getInstance(), $this) extends PluginTask
			{
				private $m_TimerInstance = null;
				private $m_Started = false;

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
//			    echo "ticking " . $this->m_TimerInstance->getDelayLeft() . " " . $this->m_TimerInstance->getTimeLeft() . " " . $this->m_Started . "\n";
					if ($this->m_TimerInstance->getDelayLeft() > 0)
						$this->m_TimerInstance->_modDelay(-1);
					else if ($this->m_TimerInstance->getTickLeft() > 0)
					{
						if (!$this->m_TimerInstance->isPaused())
						{
							if (!$this->m_Started)
							{
								$this->m_TimerInstance->_onStart();
								$this->m_Started = true;
							}
							$this->m_TimerInstance->_onTick();

							if ($this->m_TimerInstance->getTickLeft() % 20 == 0)
								$this->m_TimerInstance->_onSecond();

							$this->m_TimerInstance->_modTime(-1);
						}
					} else
					{
						FatUtils::getInstance()->getServer()->getScheduler()->cancelTask($this->getTaskId());
						$this->m_TimerInstance->_onStop();
					}
				}
			}, 1);
		}

        return $this;
	}

    /* PLEASE DON'T USE THAT, it's intended for package scope */
	public function _onStart()
    {
        if (!is_null($this->getStartCallback()) && gettype($this->getStartCallback()) === "array")
		{
			foreach ($this->getStartCallback() as $l_Callback)
			{
				if (is_callable($l_Callback))
            		call_user_func($l_Callback);
			}
		}
    }

    /* PLEASE DON'T USE THAT, it's intended for package scope */
    public function _onTick()
    {
		if (!is_null($this->getTickCallback()) && gettype($this->getTickCallback()) === "array")
		{
			foreach ($this->getTickCallback() as $l_Callback)
			{
				if (is_callable($l_Callback))
					call_user_func($l_Callback);
			}
		}
    }

	/* PLEASE DON'T USE THAT, it's intended for package scope */
	public function _onSecond()
	{
		if (!is_null($this->getSecondCallback()) && gettype($this->getSecondCallback()) === "array")
		{
			foreach ($this->getSecondCallback() as $l_Callback)
			{
				if (is_callable($l_Callback))
					call_user_func($l_Callback);
			}
		}
	}

    /* PLEASE DON'T USE THAT, it's intended for package scope */
    public function _onStop()
    {
		if (!is_null($this->getStopCallback()) && gettype($this->getStopCallback()) === "array")
		{
			foreach ($this->getStopCallback() as $l_Callback)
			{
				if (is_callable($l_Callback))
					call_user_func($l_Callback);
			}
		}

        $this->m_Task = null;
    }
}