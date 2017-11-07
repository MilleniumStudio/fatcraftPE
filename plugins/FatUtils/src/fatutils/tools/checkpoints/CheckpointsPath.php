<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 06/11/2017
 * Time: 14:40
 */

namespace fatutils\tools\checkpoints;


use fatutils\FatUtils;
use fatutils\tools\LoopedExec;
use pocketmine\level\sound\GenericSound;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class CheckpointsPath
{
	/** @var Checkpoint[]  */
	private $m_Checkpoints = [];

	private $m_PlayerDatas = [];

	private $m_StartCallbacks = [];
	private $m_CheckpointCallbacks = [];
	private $m_LapCompleteCallbacks = [];
	private $m_EndCallbacks = [];

	private $m_LapToFinish = 1;
	private $m_CheckpointCount = 0;

	private $m_Enable = true;

	public function __construct()
	{
		new LoopedExec(function () {
			foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
			{
			    $l_PlayerData = $this->getPlayerData($l_Player);
			    if (!is_null($l_PlayerData))
				{
					if ($l_PlayerData->getNextCheckpoint() != null)
					{
						$l_PlayerData->getNextCheckpoint()->displayAsNext($l_Player);
						$l_AfterNext = $l_PlayerData->getNextCheckpoint()->getNextCheckpoint();
						if ($l_AfterNext != null)
							$l_AfterNext->displayAsAfterNext($l_Player);
					}
				} else if ($this->getCheckpointCount() > 0) {
			    	$this->getCheckpointAtIndex(0)->displayAsNext($l_Player);
				}
			}
		}, 5);
	}

	//-----------------------
	// UTILS
	//-----------------------
	public function addCheckpoint(Checkpoint $p_Checkpoint, int $p_Index = null): CheckpointsPath
	{
		$p_Checkpoint->setCheckpointsPath($this);

		if ($p_Index == null || $p_Index > count($this->m_Checkpoints))
			$this->m_Checkpoints[] = $p_Checkpoint;
		else
			array_splice($this->m_Checkpoints, $p_Index, 0, [$p_Checkpoint]);

		$this->m_CheckpointCount = count($this->m_Checkpoints);

		return $this;
	}

	/** @param Callable (Player)
	 * @return CheckpointsPath
	 */
	public function addStartCallback(Callable $p_Callable): CheckpointsPath
	{
		$this->m_StartCallbacks[] = $p_Callable;

		return $this;
	}

	/** @param Callable (Player, Checkpoint)
	 * @return CheckpointsPath
	 */
	public function addCheckpointCallback(Callable $p_Callable): CheckpointsPath
	{
		$this->m_CheckpointCallbacks[] = $p_Callable;

		return $this;
	}

	/** @param Callable (Player, int)
	 * @return CheckpointsPath
	 */
	public function addLapCompleteCallback(Callable $p_Callable): CheckpointsPath
	{
		$this->m_LapCompleteCallbacks[] = $p_Callable;

		return $this;
	}

	/** @param Callable (Player)
	 * @return CheckpointsPath
	 */
	public function addEndCallback(Callable $p_Callable): CheckpointsPath
	{
		$this->m_EndCallbacks[] = $p_Callable;

		return $this;
	}

	public function resetPlayerData(Player $p_Player)
	{
		$l_Key = $p_Player->getUniqueId()->toString();
		if (array_key_exists($l_Key, $this->m_PlayerDatas))
			unset($this->m_PlayerDatas[$l_Key]);
	}

	public function enable(): CheckpointsPath
	{
		$this->m_Enable = true;
		return $this;
	}

	public function disable(): CheckpointsPath
	{
		$this->m_Enable = false;
		return $this;
	}

	public function _notifyCollision(Player $p_Player, Checkpoint $p_Checkpoint)
	{
		if ($this->m_Enable)
		{
			$l_PlayerData = $this->getPlayerData($p_Player);

			if ($l_PlayerData != null)
			{
				if ($l_PlayerData->getNextCheckpoint() != null)
				{
					if ($l_PlayerData->getNextCheckpoint()->equals($p_Checkpoint))
					{
						$l_PlayerData->setLastCheckpoint($p_Checkpoint);
						if ($p_Checkpoint->isEnd()) // END
						{
							if ($l_PlayerData->getCurrentLap() < $this->getLapToFinish())
							{
								$l_PlayerFinishedLapNumber = $l_PlayerData->getCurrentLap();
								$l_PlayerData->addLap();
								$l_PlayerData->setNextCheckpoint($this->getCheckpointAtIndex(0));

								echo $p_Player->getName() . " on " . $p_Checkpoint->getIndex() . " -> New Lap (LapTime[" . $l_PlayerFinishedLapNumber . "]: " . $l_PlayerData->getLapsTime()[$l_PlayerFinishedLapNumber] . ")" . "\n";
								foreach ($this->m_LapCompleteCallbacks as $l_Callback)
								{
									if (is_callable($l_Callback))
										$l_Callback($p_Player, $l_PlayerFinishedLapNumber);
								}
							} else
							{
								$l_PlayerData->addLap();
								$l_PlayerData->end();

								echo $p_Player->getName() . " on " . $p_Checkpoint->getIndex() . " -> End (TotalTime: " . $l_PlayerData->getMicroTime() . ")" . "\n";
								if ($this->getLapToFinish() > 1)
									var_dump($l_PlayerData->getLapsTime());
								foreach ($this->m_EndCallbacks as $l_Callback)
								{
									if (is_callable($l_Callback))
										$l_Callback($p_Player);
								}
							}
						} else {
							$l_PlayerData->setNextCheckpoint($p_Checkpoint->getNextCheckpoint());

							echo $p_Player->getName() . " on " . $p_Checkpoint->getIndex() . " -> NextCheckpoint" . "\n";
							foreach ($this->m_CheckpointCallbacks as $l_Callback)
							{
								if (is_callable($l_Callback))
									$l_Callback($p_Player, $p_Checkpoint);
							}
						}

						$p_Player->getLevel()->addSound(new GenericSound($p_Player, LevelEventPacket::EVENT_SOUND_ORB), [$p_Player]);
					}
				}
			} else if ($p_Checkpoint->isStart()) // START
			{
				$l_PlayerData = $this->createPlayerData($p_Player);
				$l_PlayerData->start();
				$l_PlayerData->setLastCheckpoint($p_Checkpoint);
				$l_PlayerData->setNextCheckpoint($p_Checkpoint->getNextCheckpoint());

				echo $p_Player->getName() . " on " . $p_Checkpoint->getIndex() . " -> Start" . "\n";
				foreach ($this->m_StartCallbacks as $l_Callback)
				{
					if (is_callable($l_Callback))
						$l_Callback($p_Player);
				}

				$p_Player->getLevel()->addSound(new GenericSound($p_Player, LevelEventPacket::EVENT_SOUND_ORB, 0.5), [$p_Player]);
			}
		}
	}

	private function createPlayerData(Player $p_Player):PlayerData
	{
		$l_PlayerData = new PlayerData();
		$this->m_PlayerDatas[$p_Player->getUniqueId()->toString()] = $l_PlayerData;
		return $l_PlayerData;
	}

	//-----------------------
	// SETTERS
	//-----------------------
	public function setLapToFinish(int $m_LapToFinish): CheckpointsPath
	{
		$this->m_LapToFinish = $m_LapToFinish;
		return $this;
	}

	//-----------------------
	// GETTERS
	//-----------------------
	public function getPlayerData(Player $p_Player):?PlayerData
	{
		$l_Key = $p_Player->getUniqueId()->toString();
		if (array_key_exists($l_Key, $this->m_PlayerDatas))
			return $this->m_PlayerDatas[$l_Key];

		return null;
	}

	public function getLapToFinish():int
	{
		return $this->m_LapToFinish;
	}

	public function getCheckpointAtIndex(int $p_Index): Checkpoint
	{
		return $this->m_Checkpoints[$p_Index];
	}

	public function getCheckpointIndex(Checkpoint $p_Checkpoint): int
	{
		return array_search($p_Checkpoint, $this->m_Checkpoints);
	}

	public function getCheckpointCount():int
	{
		return $this->m_CheckpointCount;
	}

	public function isEnabled():bool
	{
		return $this->m_Enable;
	}
}