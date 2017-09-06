<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 10:48
 */

namespace fatutils\players;


use pocketmine\Player;

define("STATE_WAITING", 0);
define("STATE_PLAYING", 1);

class FatPlayer
{
	private $m_Player;
	private $m_State = 0; // 0 is waiting, 1 is playing

	/**
	 * FatPlayer constructor.
	 * @param Player $p_Player
	 */
	public function __construct(Player $p_Player)
	{
		$this->m_Player = $p_Player;
	}

	public function setPlaying()
	{
		$this->m_State = STATE_PLAYING;
	}

	public function isWaiting()
	{
		return $this->m_State === STATE_WAITING;
	}

	public function isPlaying()
	{
		return $this->m_State === STATE_PLAYING;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player
	{
		return $this->m_Player;
	}
}