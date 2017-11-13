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

class CircleAnimation extends Animation
{
	private $m_Timer = null;
	private $m_Entity = null;
	private $m_Position = null;
	private $m_TickRemaining = 60;
	private $m_Radius = 2;
	private $m_NbPoint = null;
	private $m_NbSubDivision = 1;
	private $m_ClockLike = true;
	private $m_Callback = null;

	public function setEntity(Entity $p_Entity): CircleAnimation
	{
		$this->m_Entity = $p_Entity;
		return $this;
	}

	public function setPosition(Position $p_Position): CircleAnimation
	{
		$this->m_Position = $p_Position;
		return $this;
	}

	public function setTickDuration(int $p_TickDuration): CircleAnimation
	{
		$this->m_TickRemaining = $p_TickDuration;
		return $this;
	}

	public function setRadius(float $p_Radius): CircleAnimation
	{
		$this->m_Radius = $p_Radius;
		return $this;
	}

	public function setNbPoint(int $p_NbPoint): CircleAnimation
	{
		$this->m_NbPoint = $p_NbPoint;
		return $this;
	}

	public function setNbSubDivision(int $p_NbSubDivision): CircleAnimation
	{
		$this->m_NbSubDivision = $p_NbSubDivision;
		return $this;
	}

	public function setClockLike(bool $p_ClockLike): CircleAnimation
	{
		$this->m_ClockLike = $p_ClockLike;
		return $this;
	}

	/**
	 * @param callable $p_Callback => as an array of Vector3
	 * @return CircleAnimation
	 */
	public function setCallback(Callable $p_Callback): CircleAnimation
	{
		$this->m_Callback = $p_Callback;
		return $this;
	}

	public function play()
	{
		$particleOffset = 1;

		if ($this->m_Timer instanceof Timer)
			$this->m_Timer->cancel();

		if (is_null($this->m_NbPoint))
			$this->m_NbPoint = $this->m_TickRemaining * $this->m_NbSubDivision;

		$this->m_Timer = new Timer($this->m_TickRemaining);
		$this->m_Timer
			->addTickCallback(function () use (&$particleOffset)
			{
				$l_locationList = [];
				$particleOffset = ($particleOffset + ($this->m_ClockLike ? 1 : -1)) % $this->m_NbPoint;
				for ($i = 0; $i < $this->m_NbSubDivision; $i++)
				{
					if ($this->m_Entity instanceof Entity)
						$l_locationList[] = GeometryUtils::relativeToLocation($this->m_Entity->asLocation(), (float)0, (((float)360 / (float)$this->m_NbPoint) * (float)$particleOffset) + (((float)360 / (float)$this->m_NbSubDivision) * (float)$i), (float)$this->m_Radius);
					else if ($this->m_Position instanceof Position)
						$l_locationList[] = GeometryUtils::relativeToLocation(Location::fromObject($this->m_Position), (float)0, (((float)360 / (float)$this->m_NbPoint) * (float)$particleOffset) + (((float)360 / (float)$this->m_NbSubDivision) * (float)$i), (float)$this->m_Radius);
				}

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