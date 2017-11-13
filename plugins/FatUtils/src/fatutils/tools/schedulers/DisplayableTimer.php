<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 31/10/2017
 * Time: 10:36
 */

namespace fatutils\tools\schedulers;


use fatutils\tools\TextFormatter;
use pocketmine\Player;

class DisplayableTimer extends Timer
{
	protected $m_Title = "";

	public function setTitle($p_Title):DisplayableTimer
	{
		$this->m_Title = $p_Title;
		return $this;
	}

	public function toString(Player $p_Player): string
	{
		$timeFormat = gmdate("H:i:s", $this->getSecondLeft());
		if ($this->m_Title instanceof TextFormatter)
			$this->m_Title->addParam("time", $timeFormat); //ref to param "{time}" in translation lines

		if ($this->m_Title instanceof TextFormatter)
			return $this->m_Title->asStringForPlayer($p_Player);
		else
			return $this->m_Title . ": " . $timeFormat;
	}
}