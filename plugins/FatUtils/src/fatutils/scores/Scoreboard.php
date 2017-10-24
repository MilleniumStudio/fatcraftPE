<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 23/10/2017
 * Time: 11:51
 */

namespace fatutils\scores;


abstract class Scoreboard
{
	private $m_Weight = 1;

	public abstract function getBest();
	public abstract function getScores(): array;

	public function getWeight(): int
	{
		return $this->m_Weight;
	}

	public function setWeight(int $m_Weight):Scoreboard
	{
		$this->m_Weight = $m_Weight;
		return $this;
	}

	public abstract function getRatios():array;
}