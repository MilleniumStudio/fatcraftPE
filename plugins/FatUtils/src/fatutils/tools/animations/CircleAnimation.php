<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 10:23
 */

namespace fatutils\tools\animations;


use fatutils\tools\GeometryUtils;
use fatutils\tools\Timer;
use pocketmine\entity\Entity;
use pocketmine\level\Position;

class CircleAnimation extends Animation
{
	private $m_Timer = null;
	private $m_Entity = null;
	private $m_Position = null;
	private $m_TickRemaining = 60;
	private $m_Radius = 2;
	private $m_NbPoint = 10;
	private $m_NbSubDivision = 10;
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

	public function setCallback(Callable $p_Callback): CircleAnimation
	{
		$this->m_Callback = $p_Callback;
		return $this;
	}

	public function play()
	{
		$particleOffset = 1;

		$m_Timer = new Timer($this->m_TickRemaining);
		$m_Timer
			->addTickCallback(function () use (&$particleOffset)
			{
				$l_locationList = [];
				echo $particleOffset . "\n";
				$particleOffset = ($particleOffset + ($this->m_ClockLike ? 1 : -1)) % $this->m_NbPoint;
				for ($i = 0; $i < $this->m_NbSubDivision; $i++)
				{
					$l_location = null;
					if ($this->m_Entity != null)
						$l_location = GeometryUtils::relativeToPosition($this->m_Entity, (float)0, (((float)360 / (float)$this->m_NbPoint) * (float)$particleOffset) + (((float)360 / (float)$this->m_NbSubDivision) * (float)$i), $this->m_Radius);
					if ($this->m_Position != null)
						$l_location = GeometryUtils::relativeToPosition($this->m_Position, (float)0, (((float)360 / (float)$this->m_NbPoint) * (float)$particleOffset) + (((float)360 / (float)$this->m_NbSubDivision) * (float)$i), $this->m_Radius);

					$l_locationList[] = $l_location;
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
}