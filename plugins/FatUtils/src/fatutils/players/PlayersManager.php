<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 10:45
 */

namespace fatutils\players;

use fatutils\FatUtils;
use pocketmine\Player;
use pocketmine\utils\UUID;

class PlayersManager
{
	private static $m_Instance = null;
	private $m_FatPlayers = [];

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
	    $this->initialize();
	}

    public function initialize()
    {
        $this->setMinPlayer(FatUtils::getInstance()->getTemplateConfig()->get("minPlayer"));
        $this->setMaxPlayer(FatUtils::getInstance()->getTemplateConfig()->get("maxPlayer"));
        echo "Initializing PlayersManager\n";
        echo "  - minPlayers: " . $this->getMinPlayer() . "\n";
        echo "  - maxPlayers: " . $this->getMaxPlayer() . "\n";
    }

	public function addPlayer(Player $p_Player)
	{
		$this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()] = new FatPlayer($p_Player);
	}

	public function removePlayer(Player $p_Player)
	{
		if (isset($this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()]))
			unset($this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()]);
	}

	public function getFatPlayerByName(string $p_PlayerName):FatPlayer
	{
		foreach ($this->m_FatPlayers as $l_Player)
        {
            if ($l_Player instanceof FatPlayer && strcmp($l_Player->getPlayer()->getName(), $p_PlayerName) === 0)
                return $l_Player;
        }
	}

	public function getFatPlayer(Player $p_Player):FatPlayer
	{
		return $this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()];
	}

	public function getFatPlayerByUUID(UUID $p_UUID):FatPlayer
	{
		return $this->m_FatPlayers[$p_UUID->toBinary()];
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