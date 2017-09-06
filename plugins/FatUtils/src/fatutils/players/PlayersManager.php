<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 10:45
 */

namespace fatutils\players;

use pocketmine\Player;
use pocketmine\utils\UUID;

class PlayersManager
{
	private static $m_Instance = null;
	private $m_Players = [];

	private $m_MinPlayer = 0;
	private $m_MaxPlayer = 18;

	public static function getInstance(): PlayersManager
	{
		if (is_null(self::$m_Instance))
			self::$m_Instance = new PlayersManager();
		return self::$m_Instance;
	}

	/**
	 * PlayersManager constructor.
	 */
	private function __construct()
	{
	}

	public function addPlayer(Player $p_Player)
	{
		$this->m_Players[$p_Player->getUniqueId()->toBinary()] = new FatPlayer($p_Player);
	}

	public function removePlayer(Player $p_Player)
	{
		if (isset($this->m_Players[$p_Player->getUniqueId()->toBinary()]))
			unset($this->m_Players[$p_Player->getUniqueId()->toBinary()]);
	}

	public function getFatPlayerByName(string $p_Player):FatPlayer
	{
		echo "plops";//TODO
	}

	public function getFatPlayer(Player $p_Player):FatPlayer
	{
		return $this->m_Players[$p_Player->getUniqueId()->toBinary()];
	}

	public function getFatPlayerByUUID(UUID $p_UUID):FatPlayer
	{
		return $this->m_Players[$p_UUID->toBinary()];
	}

	//----------------
	// GETTERS
	//----------------
	/**
	 * @return int
	 */
	public function getMinPlayer(): int
	{
		return $this->m_MinPlayer;
	}

	/**
	 * @return int
	 */
	public function getMaxPlayer(): int
	{
		return $this->m_MaxPlayer;
	}

	public function setMinPlayer($p_MinPlayer)
	{
		$this->m_MinPlayer = $p_MinPlayer;
	}

	public function setMaxPlayer($p_MaxPlayer)
	{
		$this->m_MaxPlayer = $p_MaxPlayer;
	}
}