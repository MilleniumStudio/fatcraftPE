<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 23/10/2017
 * Time: 11:51
 */

namespace fatutils\scores;


use fatutils\players\PlayersManager;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use pocketmine\Player;
use pocketmine\utils\UUID;

class TeamScoreboard extends Scoreboard
{
	/** @var array TeamName => int */
	private $m_Scores = [];

	public function getBest():?Team
	{
		$l_Scores = $this->getScores();
		asort($l_Scores);
		reset($l_Scores);

		return count($l_Scores) > 0 ? TeamsManager::getInstance()->getTeamByName(key($l_Scores)) : null;
	}

	public function getTeamScore(Team $p_Team):int
	{
		return $this->getTeamNameScore($p_Team->getName());
	}

	public function getTeamNameScore(string $p_TeamName):int
	{
		if (array_key_exists($p_TeamName, $this->m_Scores))
			return $this->m_Scores[$p_TeamName];
		else
			return 0;
	}

	public function addTeamScore(Team $p_Team, int $p_Score)
	{
		$this->addTeamNameScore($p_Team->getName(), $p_Score);
	}

	public function addTeamNameScore(string $p_TeamName, int $p_Score)
	{
		if (array_key_exists($p_TeamName, $this->m_Scores))
			$this->m_Scores[$p_TeamName] = $this->m_Scores[$p_TeamName] + $p_Score;
		else
			$this->m_Scores[$p_TeamName] = $p_Score;
	}

	public function getScores(): array
	{
		return $this->m_Scores;
	}

	public function getRatios(): array
	{
		// TODO: Implement getRatios() method.
	}
}