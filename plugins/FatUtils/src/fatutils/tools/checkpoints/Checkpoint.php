<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 06/11/2017
 * Time: 14:40
 */

namespace fatutils\tools\checkpoints;

use pocketmine\Player;

abstract class Checkpoint
{
	protected static $m_IdAutoIncrement = 0;
	protected $m_Id;

	protected $m_Index = null;

	private $m_CheckpointsPath = null;

	/**
	 * Checkpoint constructor.
	 */
	public function __construct()
	{
		$this->m_Id = self::$m_IdAutoIncrement++;
	}

	public function setCheckpointsPath(CheckpointsPath $p_Path)
	{
		$this->m_CheckpointsPath = $p_Path;
	}

	public function getCheckpointsPath():CheckpointsPath
	{
		return $this->m_CheckpointsPath;
	}

	public function notifyCollision(Player $p_Player)
	{
		$this->getCheckpointsPath()->_notifyCollision($p_Player, $this);
	}

	public function getIndex(): int
	{
		if ($this->m_Index == null)
			$this->m_Index = $this->getCheckpointsPath()->getCheckpointIndex($this);
		return $this->m_Index;
	}

	public function getNextCheckpoint():?Checkpoint
	{
		if ($this->isEnd())
			return null;
		else
			return $this->getCheckpointsPath()->getCheckpointAtIndex($this->getIndex() + 1);
	}

	public function isStart(): bool
	{
		return $this->getIndex() === 0;
	}

	public function isEnd(): bool
	{
		return $this->getIndex() === $this->getCheckpointsPath()->getCheckpointCount() - 1;
	}

	public function equals(Checkpoint $p_Checkpoint): bool
	{
		return $p_Checkpoint->m_Id === $this->m_Id;
	}

	//-----------------------
	// ABSTRACT
	//-----------------------
	public abstract function displayAsNext(Player $p_Player = null);

	public abstract function displayAsAfterNext(Player $p_Player = null);
}