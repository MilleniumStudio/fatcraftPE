<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 06/11/2017
 * Time: 15:01
 */

namespace fatutils\tools\checkpoints;

use fatutils\tools\TimeUtils;

class PlayerData
{
	private $m_StartTime = null;
	private $m_EndTime = null;

	private $m_LastCheckpoint = null;
	private $m_NextCheckpoint = null;

	private $m_CurrentLap = 1;
	private $m_LapsTime = [];

	//-----------------------
	// UTILS
	//-----------------------
	public function start()
	{
		$this->m_StartTime = TimeUtils::getCurrentMillisec();
	}

	public function end()
	{
		$this->m_EndTime = TimeUtils::getCurrentMillisec();
		$this->m_NextCheckpoint = null;
	}

	/**
	 * When the player has finish all laps
	 * @return bool
	 */
	public function hasFinish():bool
	{
		return !is_null($this->m_EndTime);
	}

	public function setLastCheckpoint(?Checkpoint $p_Checkpoint)
	{
		$this->m_LastCheckpoint = $p_Checkpoint;
	}

	public function setNextCheckpoint(?Checkpoint $p_Checkpoint)
	{
		$this->m_NextCheckpoint = $p_Checkpoint;
	}

	public function addLap(int $p_Mod = 1)
	{
		if (count($this->m_LapsTime) == 0)
			$this->m_LapsTime[$this->m_CurrentLap] = $this->getMicroTime();
		else
		{
			$l_OtherLapTotal = 0;
			foreach ($this->m_LapsTime as $l_LapTime)
				$l_OtherLapTotal += $l_LapTime;

			$this->m_LapsTime[$this->m_CurrentLap] = $this->getMicroTime() - $l_OtherLapTotal;
		}
		$this->m_CurrentLap += $p_Mod;
	}

	//-----------------------
	// GETTERS
	//-----------------------
	public function getMicroTime(): int
	{
		if (is_null($this->m_StartTime))
			return 0;
		else if (is_null($this->m_EndTime))
		{
			return TimeUtils::getCurrentMillisec() - $this->m_StartTime;
		} else
			return $this->m_EndTime - $this->m_StartTime;
	}

	public function getCurrentLap(): int
	{
		return $this->m_CurrentLap;
	}

	/**
	 * @return array
	 */
	public function getLapsTime(): array
	{
		return $this->m_LapsTime;
	}

	public function getLastCheckpoint():?Checkpoint
	{
		return $this->m_LastCheckpoint;
	}

	public function getNextCheckpoint():?Checkpoint
	{
		return $this->m_NextCheckpoint;
	}
}