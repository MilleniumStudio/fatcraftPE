<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 10:23
 */

namespace fatutils\tools\animations;


use fatutils\tools\GeometryUtils;
use fatutils\tools\schedulers\Timer;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\level\Position;

class ShockWaveAnimation extends Animation
{
	private $m_Timer = null;
	private $m_Location = null;
	private $m_TickRemaining = 60;
	private $m_StartRadius = 1;
	private $m_FinalRadius = 2;
	private $m_NbPointPerCircle = 10;
	private $m_NbCirclePerTick = 1;
	private $m_Callback = null;

	public function __construct(Location $p_Location)
	{
		$this->m_Location = $p_Location;
	}

	public function setTickDuration(int $p_TickDuration): ShockWaveAnimation
	{
		$this->m_TickRemaining = $p_TickDuration;
		return $this;
	}

	public function setStartRadius(float $p_Radius): ShockWaveAnimation
	{
		$this->m_StartRadius = $p_Radius;
		return $this;
	}

	public function setFinalRadius(float $p_Radius): ShockWaveAnimation
	{
		$this->m_FinalRadius = $p_Radius;
		return $this;
	}

	public function setNbPointInACircle(int $p_NbPointPerCircle): ShockWaveAnimation
	{
		$this->m_NbPointPerCircle = $p_NbPointPerCircle;
		return $this;
	}

	public function setNbCirclePerTick(int $p_NbCirclePerTick): ShockWaveAnimation
	{
		$this->m_NbCirclePerTick = $p_NbCirclePerTick;
		return $this;
	}

	/**
	 * @param callable $p_Callback =>  as an array of Vector3
	 * @return ShockWaveAnimation
	 */
	public function setCallback(Callable $p_Callback): ShockWaveAnimation
	{
		$this->m_Callback = $p_Callback;
		return $this;
	}

	public function play()
	{
		$l_ProgressiveRadius = $this->m_StartRadius;
		$l_DistanceByTick = $this->m_FinalRadius / (float)$this->m_TickRemaining;
		$l_NbCircle = 0;

		$this->m_Timer = new Timer($this->m_TickRemaining);
		$this->m_Timer
			->addTickCallback(function () use (&$l_NbCircle, &$l_ProgressiveRadius, $l_DistanceByTick)
			{
				$l_locationList = [];
				for ($c = 0; $c < $this->m_NbCirclePerTick; $c++)
				{
					$l_Distance = ($l_ProgressiveRadius + ($l_DistanceByTick * (float)$c / (float)$this->m_NbCirclePerTick));
					$l_AngleStep = (float)360 / (float)($this->m_NbPointPerCircle + $l_NbCircle);

					for ($i = 0, $l = $this->m_NbPointPerCircle + $l_NbCircle; $i < $l; $i++)
					{
						$l_location = GeometryUtils::relativeToLocation($this->m_Location, 0, $l_AngleStep * $i, $l_Distance);
						$l_locationList[] = $l_location;
					}
					$l_NbCircle++;
				}
				$l_ProgressiveRadius += $l_DistanceByTick;

				if (is_callable($this->m_Callback))
					call_user_func($this->m_Callback, $l_locationList);
			})
			->start();
	}

	public function pause()
	{
		if ($this->m_Timer instanceof Timer)
			$this->m_Timer->pause();
	}

	public function stop()
	{
		if ($this->m_Timer instanceof Timer)
			$this->m_Timer->cancel();
	}

	public function isRunning()
	{
		return $this->m_Timer instanceof Timer && $this->m_Timer->getTickLeft() > 0;
	}
}