<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 23/10/2017
 * Time: 11:51
 */

namespace fatutils\scores;


use fatutils\players\PlayersManager;
use pocketmine\Player;
use pocketmine\utils\UUID;

class PlayerScoreboard extends Scoreboard
{
	/** @var array UUID => int */
	private $m_Scores = [];

	public function getBest(): ?UUID
	{
		$l_Scores = $this->getScores();
		asort($l_Scores);
		$l_Scores = array_reverse($l_Scores, true);
		reset($l_Scores);

		return count($l_Scores) > 0 ? UUID::fromString(key($l_Scores)) : null;
	}

	public function getPlayerScore(Player $p_Player):int
	{
		return $this->getUuidScore($p_Player->getUniqueId());
	}

	public function getUuidScore(UUID $p_PlayerUuid):int
	{
		$l_Key = $p_PlayerUuid->toString();
		if (array_key_exists($l_Key, $this->m_Scores))
			return $this->m_Scores[$l_Key];
		else
			return 0;
	}

	public function addPlayerScore(Player $p_Player, int $p_Score)
	{
		$this->addUuidScore($p_Player->getUniqueId(), $p_Score);
	}

	public function addUuidScore(UUID $p_PlayerUUID, int $p_Score)
	{
		$l_Key = $p_PlayerUUID->toString();
		if (array_key_exists($l_Key, $this->m_Scores))
			$this->m_Scores[$l_Key] = $this->m_Scores[$l_Key] + $p_Score;
		else
			$this->m_Scores[$l_Key] = $p_Score;
	}

	public function getScores(): array
	{
		return $this->m_Scores;
	}

	public function getRatios(): array
	{
		$l_BestUuid = $this->getBest();
		if ($l_BestUuid instanceof UUID)
			$l_BestScore = $this->getUuidScore($this->getBest());
		else
			$l_BestScore = 1;

		$l_Ret = [];
		foreach ($this->m_Scores as $l_Key => $l_Score)
		{
			if ($l_BestScore > 0)
				$l_Ret[$l_Key] = (float)$l_Score / (float)$l_BestScore;
			else
				$l_Ret[$l_Key] = 0;
		}

		return $l_Ret;
	}
}