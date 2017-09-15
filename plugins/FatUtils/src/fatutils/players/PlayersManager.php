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
use fatutils\gamedata\GameDataManager;

class PlayersManager
{
    const CONFIG_KEY_MAX_PLAYER = "maxPlayer";
    const CONFIG_KEY_MIN_PLAYER = "minPlayer";

	private static $m_Instance = null;
	private $m_FatPlayers = [];

	private $m_MinPlayer = 0;
	private $m_MaxPlayer = 18;

    private $m_DisplayHealth = false;

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
        if (!is_null(FatUtils::getInstance()->getTemplateConfig()))
        {
            $this->setMinPlayer(FatUtils::getInstance()->getTemplateConfig()->get(PlayersManager::CONFIG_KEY_MIN_PLAYER));
            $this->setMaxPlayer(FatUtils::getInstance()->getTemplateConfig()->get(PlayersManager::CONFIG_KEY_MAX_PLAYER));
            echo "Initializing PlayersManager\n";
            echo "  - minPlayers: " . $this->getMinPlayer() . "\n";
            echo "  - maxPlayers: " . $this->getMaxPlayer() . "\n";
        }
    }

	public function addPlayer(Player $p_Player)
	{
		$this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()] = new FatPlayer($p_Player);
                GameDataManager::getInstance()->recordJoin($p_Player->getUniqueId(), $p_Player->getAddress());
	}

	public function removePlayer(Player $p_Player)
	{
		if (isset($this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()]))
			unset($this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()]);
                GameDataManager::getInstance()->recordLeave($p_Player->getUniqueId());
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

	public function getAlivePlayerLeft(): int
    {
        $i = 0;
        foreach ($this->m_FatPlayers as $l_FatPlayer)
        {
            if ($l_FatPlayer instanceof FatPlayer && !$l_FatPlayer->hasLost())
                $i++;
        }
        return $i;
    }

    public function getAlivePlayers(): array
    {
        $l_Ret = [];
        foreach ($this->m_FatPlayers as $l_FatPlayer)
        {
            if ($l_FatPlayer instanceof FatPlayer && !$l_FatPlayer->hasLost())
                $l_Ret[] = $l_FatPlayer;
        }
        return $l_Ret;
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

    public function displayHealth(bool $p_Value = true)
    {
        $this->m_DisplayHealth = $p_Value;
    }

    public function isHealthDisplayed(): bool
    {
        return $this->m_DisplayHealth;
    }
}