<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 10:45
 */

namespace fatutils\players;

use fatutils\FatUtils;
use fatutils\tools\TextFormatter;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\UUID;
use fatutils\gamedata\GameDataManager;
use libasynql\DirectQueryMysqlTask;
use fatcraft\loadbalancer\LoadBalancer;


class PlayersManager
{
	const CONFIG_KEY_MAX_PLAYER = "maxPlayer";
	const CONFIG_KEY_MIN_PLAYER = "minPlayer";

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
        if (LoadBalancer::getInstance()->getServerType() == "shop")
            LoadBalancer::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "buycraft secret c3ff65408c433494f06bcd411bc6399e03fb6c6c");
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
		\fatcraft\loadbalancer\LoadBalancer::getInstance()->connectMainThreadMysql()->query("CREATE TABLE IF NOT EXISTS `players` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `uuid` VARCHAR(36) NOT NULL,
            `xuid` VARCHAR(36) NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `email` VARCHAR(50) DEFAULT NULL,
            `fsaccount` VARCHAR(50) DEFAULT NULL,
            `lang` INT(3) NOT NULL DEFAULT '0' COMMENT '0 en, 1 fr, 2 es',
            `permission_group` varchar(50) DEFAULT NULL,
            `join_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `fatsilver` INT(11) DEFAULT 0,
            `fatgold` INT(11) DEFAULT 0,
            `muted` TIMESTAMP DEFAULT NULL,
            `shop_possessed` TEXT DEFAULT NULL,
            `shop_equipped` TEXT DEFAULT NULL,
            PRIMARY KEY (`id`));
       	");

    }

	public function addPlayer(Player $p_Player)
	{
		FatUtils::getInstance()->getLogger()->info("Creating FatPlayer for " . $p_Player->getName() . "(" . $p_Player->getUniqueId()->toString() . ")");
		$this->m_FatPlayers[$p_Player->getUniqueId()->toString()] = new FatPlayer($p_Player);
		GameDataManager::getInstance()->recordJoin($p_Player->getUniqueId(), $p_Player->getAddress());

        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "INSERT INTO players_connection_log (player_uuid, player_name, server_type, time) VALUES(?, ?, ?, ?);", [
                    ["s", $p_Player->getUniqueId()],
                    ["s", $p_Player->getName()],
                    ["s", LoadBalancer::getInstance()->getServerType()],
                    ["s", date("Y-m-d H:i:s")]
                ]));
	}

	public function changeAllInGamePlayerGamemode(int $p_Gamemode)
    {
        foreach ($this->m_FatPlayers as $l_FatPlayer)
        {
            if ($l_FatPlayer instanceof FatPlayer && !$l_FatPlayer->isOutOfGame())
                $l_FatPlayer->getPlayer()->setGamemode($p_Gamemode);
        }
    }

	public function removePlayer(Player $p_Player)
	{
		$key = $p_Player->getUniqueId()->toString();
		if (isset($this->m_FatPlayers[$key]))
			unset($this->m_FatPlayers[$key]);
		GameDataManager::getInstance()->recordLeave($p_Player->getUniqueId());
	}

	public function fatPlayerExist(Player $p_Player)
	{
		return isset($this->m_FatPlayers[$p_Player->getUniqueId()->toString()]);
	}

	public function getFatPlayerByName(string $p_PlayerName):?FatPlayer
	{
		foreach ($this->m_FatPlayers as $l_FatPlayer)
		{
			if ($l_FatPlayer instanceof FatPlayer && strcmp($l_FatPlayer->getPlayer()->getName(), $p_PlayerName) === 0)
				return $l_FatPlayer;
		}
		return null;
	}

	public function sendMessageToOnline($p_Message)
	{
		foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
		{
			if ($p_Message instanceof TextFormatter)
				$l_Player->sendMessage($p_Message->asStringForPlayer($l_Player));
			else
				$l_Player->sendMessage($p_Message);
		}
	}

	/**
	 * @return FatPlayer[]
	 */
	public function getFatPlayers(): array
	{
		return $this->m_FatPlayers;
	}

	public function getFatPlayer(Player $p_Player): ?FatPlayer
	{
		$key = $p_Player->getUniqueId()->toString();
		if ($p_Player->getLevel() == null)
		    return null;
		if (!isset($this->m_FatPlayers[$key]))
			$this->addPlayer($p_Player);

		return $this->m_FatPlayers[$key];
	}

	public function getFatPlayerByUUID(UUID $p_UUID):?FatPlayer
	{
		if (array_key_exists($p_UUID->toString(), $this->m_FatPlayers))
			return $this->m_FatPlayers[$p_UUID->toString()];
		else
			return null;
	}

	public function getPlayerFromUUID(UUID $p_PlayerUUID):?Player
	{
		foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $player)
		{
			if ($player->getUniqueId()->equals($p_PlayerUUID))
				return $player;
		}

		return null;
	}

	public function getInGamePlayerLeft(): int
	{
		$i = 0;
		foreach ($this->m_FatPlayers as $l_FatPlayer)
		{
			if ($l_FatPlayer instanceof FatPlayer && !$l_FatPlayer->isOutOfGame())
				$i++;
		}
		return $i;
	}

	public function getInGamePlayers(): array
	{
		$l_Ret = [];
		foreach ($this->m_FatPlayers as $l_FatPlayer)
		{
			if ($l_FatPlayer instanceof FatPlayer && !$l_FatPlayer->isOutOfGame())
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
                if ($p_MaxPlayer != 0)
                    \fatcraft\loadbalancer\LoadBalancer::getInstance()->setMaxPlayers($p_MaxPlayer);
	}

	public function removeFatPlayer(Player $p_Player)
	{
		$key = $p_Player->getUniqueId()->toString();

		if (isset($this->m_FatPlayers[$key]))
        {
            $l_fatPlayer = $this->m_FatPlayers[$key];
            if ($l_fatPlayer instanceof FatPlayer)
                LoadBalancer::getInstance()->getServer()->removePlayer($l_fatPlayer->getPlayer());
            unset($this->m_FatPlayers[$key]);
        }
	}
}
