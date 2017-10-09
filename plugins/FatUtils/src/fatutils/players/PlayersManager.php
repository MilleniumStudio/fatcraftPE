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
            FatUtils::getInstance()->getLogger()->info("Initializing PlayersManager");
            FatUtils::getInstance()->getLogger()->info("  - minPlayers: " . $this->getMinPlayer());
            FatUtils::getInstance()->getLogger()->info("  - maxPlayers: " . $this->getMaxPlayer());
        }
    }

	public function addPlayer(Player $p_Player)
	{
	    FatUtils::getInstance()->getLogger()->info("Creating FatPlayer for " . $p_Player->getName());
        $this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()] = new FatPlayer($p_Player);
        GameDataManager::getInstance()->recordJoin($p_Player->getUniqueId(), $p_Player->getAddress());
	}

	public function removePlayer(Player $p_Player)
	{
        $key = $p_Player->getUniqueId()->toBinary();
		if (isset($this->m_FatPlayers[$key]))
			unset($this->m_FatPlayers[$key]);
        GameDataManager::getInstance()->recordLeave($p_Player->getUniqueId());
	}

    public function fatPlayerExist(Player $p_Player)
    {
        return isset($this->m_FatPlayers[$p_Player->getUniqueId()->toBinary()]);
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
	    $key = $p_Player->getUniqueId()->toBinary();
	    if (!isset($this->m_FatPlayers[$key]))
            $this->addPlayer($p_Player);

		return $this->m_FatPlayers[$key];
	}

	public function getFatPlayerByUUID(UUID $p_UUID):?FatPlayer
	{
		return $this->m_FatPlayers[$p_UUID->toBinary()];
	}

	public function getPlayerFromUUID(UUID $p_PlayerUUID):?Player
    {
        foreach(FatUtils::getInstance()->getServer()->getOnlinePlayers() as $player){
            if($player->getUniqueId()->equals($p_PlayerUUID))
                return $player;
        }

        return null;
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
