<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 10:48
 */

namespace fatutils\players;

use fatutils\permission\PermissionManager;
use fatutils\spawns\Spawn;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use fatutils\tools\DelayedExec;
use fatutils\tools\TextFormatter;
use fatutils\ui\impl\LanguageWindow;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\tools\Timer;
use fatutils\events\LanguageUpdatedEvent;
use libasynql\result\MysqlResult;
use libasynql\DirectQueryMysqlTask;

class FatPlayer
{
	const PLAYER_STATE_WAITING = 0;
	const PLAYER_STATE_PLAYING = 1;

	public static $m_OptionDisplayHealth = true;
	public static $m_OptionDisplayGroupPrefix = true;
	public static $m_OptionDisplayTeamPrefix = true;

	private $m_Player;
	private $m_Name;
	private $m_State = 0;
	private $m_HasLost = false;
	private $m_DisplayHealth = null;

	private $m_Scores = [];
	private $m_Data = [];

	private $m_Spawn = null;
	private $m_Language = TextFormatter::LANG_ID_DEFAULT;
	private $m_Email = null;
	private $m_permissionGroup = "default";
    private $m_FSAccount = null;

	private $m_MutedTimestamp = 0;

	/**
	 * FatPlayer constructor.
	 * @param Player $p_Player
	 */
	public function __construct(Player $p_Player)
	{
		$this->setPlayer($p_Player);
	}

	public function setPlayer(Player $p_Player)
	{
		$this->m_Player = $p_Player;
		$this->m_Name = $p_Player->getName();
		$this->initData();
	}

	public function setPlaying()
	{
		$this->m_State = FatPlayer::PLAYER_STATE_PLAYING;
	}

	public function isWaiting()
	{
		return $this->m_State === FatPlayer::PLAYER_STATE_WAITING;
	}

	public function isPlaying()
	{
		return $this->m_State === FatPlayer::PLAYER_STATE_WAITING;
	}

	public function hasLost()
	{
		return $this->m_HasLost;
	}

	public function setHasLost(bool $p_HasLost = true)
	{
		$this->m_HasLost = $p_HasLost;
	}

	public function displayHealth(bool $p_Value = true)
	{
		$this->m_DisplayHealth = $p_Value;
	}

	public function addData(string $p_Key, $value)
	{
		$l_OldData = $this->getData($p_Key, 0);
		if (is_numeric($l_OldData))
			$this->m_Data[$p_Key] = $l_OldData + $value;
	}

	public function setData(string $p_Key, $value)
	{
		$this->m_Data[$p_Key] = $value;
	}

	public function getData(string $p_Key, $p_DefaultValue)
	{
		if (array_key_exists($p_Key, $this->m_Data))
			return $this->m_Data[$p_Key];
		else
			return $p_DefaultValue;
	}

	public function getDatas(): array
	{
		return $this->m_Data;
	}

	public function getTeam(): ?Team
	{
		return TeamsManager::getInstance()->getPlayerTeam($this->getPlayer());
	}

	public function getSpawn(): ?Spawn
	{
		return $this->m_Spawn;
	}

	public function getSpawnPosition(): ?Position
	{
		return (!is_null($this->getSpawn()) ? $this->getSpawn()->getLocation() : $this->getPlayer()->getLevel()->getSpawnLocation());
	}

	public function setSpawn(Spawn $p_Spawn)
	{
		$this->m_Spawn = $p_Spawn;
	}

	public function addScore(string $p_Key, int $p_Value)
	{
		if (!isset($this->m_Scores[$p_Key]))
			$this->m_Scores[$p_Key] = $p_Value;
		else
		{
			$l_OldValue = $this->m_Scores[$p_Key];
			if (is_numeric($l_OldValue))
				$this->m_Scores[$p_Key] = $l_OldValue + $p_Value;
		}
	}

	public function getScores(): array
	{
		return $this->m_Scores;
	}

	public function getName(): string
	{
		return $this->m_Name;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player
	{
		return $this->m_Player;
	}

	public function updatePlayerNames()
	{
		$l_Ret = "";

		// TEAM PREFIX
		$l_Team = TeamsManager::getInstance()->getPlayerTeam($this->getPlayer());
		if (self::$m_OptionDisplayTeamPrefix && isset($l_Team))
			$l_Ret .= $l_Team->getPrefix() . TextFormat::WHITE . TextFormat::RESET;

		// GROUP PREFIX
		if (self::$m_OptionDisplayGroupPrefix)
		{
			$l_GroupPrefix = PermissionManager::getInstance()->getFatPlayerGroupPrefix($this);
			if (strlen($l_GroupPrefix) > 0)
				$l_Ret .= TextFormat::RESET . TextFormat::GRAY . "[" . TextFormat::WHITE . $l_GroupPrefix . TextFormat::RESET . TextFormat::GRAY . "]";
		}

		$l_Ret .= TextFormat::WHITE . $this->getPlayer()->getName() . TextFormat::RESET . TextFormat::WHITE;

		$this->getPlayer()->setDisplayName($l_Ret);

		// HEALTH BAR
		if (self::$m_OptionDisplayHealth)
		{
			$l_HealthBar = "\n[" . TextFormat::RED;
			$l_PlayerHealth = $this->getPlayer()->getHealth() * 10 / $this->getPlayer()->getMaxHealth();
			for ($i = 0; $i < 10; $i++)
			{
				if ($l_PlayerHealth > 0)
				{
					$l_HealthBar .= "█";
					$l_PlayerHealth--;
				} else
					$l_HealthBar .= " ";
			}
			$l_HealthBar .= TextFormat::RESET . "]";

			$l_Ret .= $l_HealthBar;
		}

		$this->getPlayer()->setNameTag($l_Ret);
	}

    private function initData()
    {
    	$l_StartMillisec = microtime(true);
        $l_Exist = false;
        $result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
            "SELECT * FROM players WHERE uuid = ?", [
                ["s", $this->getPlayer()->getUniqueId()]
        ]);
		$l_EndMillisec = microtime(true);
        if (($result instanceof \libasynql\result\MysqlSelectResult) and count($result->rows) == 1)
        {
            if (count($result->rows) == 1)
            {
                $this->m_Email = $result->rows[0]["email"];
                $this->m_Language = $result->rows[0]["lang"];
                $this->m_permissionGroup = $result->rows[0]["permission_group"];
                if($this->m_permissionGroup == null || $this->m_permissionGroup == "")
                    $this->m_permissionGroup = "default";
                $this->m_MutedTimestamp = $result->rows[0]["muted"];
                $l_Exist = true;
                FatUtils::getInstance()->getLogger()->info("[FatPlayer] " . $this->getPlayer()->getName() . " exist in database, loading took " . (($l_EndMillisec - $l_StartMillisec) * 1000) . "ms");
            }
        }
        if (! $l_Exist)
        {
            FatUtils::getInstance()->getLogger()->info("[FatPlayer] " . $this->getPlayer()->getName() . " not exist in database, creating...");
            FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
                new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                    "INSERT INTO players (name, uuid, xuid) VALUES (?, ?, ?)", [
                    ["s", $this->getPlayer()->getName()],
                    ["s", $this->getPlayer()->getUniqueId()],
                    ["s", $this->getPlayer()->getXuid()]
                ]
            ));

            // process first login
			new DelayedExec(40, function ()
			{
				new LanguageWindow($this->getPlayer());
			});
        }

    	PermissionManager::getInstance()->updatePermissions($this);
    }

	public function getEmail()
	{
		return $this->m_Email;
	}

    public function setEmail(string $p_Email)
    {
        $this->m_Email = $p_Email;
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "UPDATE players SET email = ? WHERE uuid = ?", [
                ["s", $this->m_Email],
                ["s", $this->getPlayer()->getUniqueId()]
            ]
        ));
    }

    public function getLanguage():int
    {
        return $this->m_Language;
    }

    public function setLanguage(int $p_Language): bool
    {
        if ($this->m_Language != $p_Language)
        {
            $this->m_Language = $p_Language;
            FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
                new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                    "UPDATE players SET lang = ? WHERE uuid = ?", [
                    ["i", $this->m_Language],
                    ["s", $this->getPlayer()->getUniqueId()]
                ]
            ));
            FatUtils::getInstance()->getServer()->getPluginManager()->callEvent(new LanguageUpdatedEvent($this->getPlayer(), $this->m_Language));
            return true;
        }
        return false;
    }

    public function getPermissionGroup(){
	    return $this->m_permissionGroup;
    }

    public function setPermissionGroup(string $p_groupName){
        $this->m_permissionGroup = $p_groupName;
        MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(), "UPDATE players SET permission_group = ? WHERE uuid = ?", [
            ["s", $this->m_permissionGroup],
            ["s", $this->getPlayer()->getUniqueId()]
        ]);
    }

    public function getFSAccount()
    {
        return $this->m_FSAccount;
    }

    public function setFSAccount(string $p_FSAccount)
    {
        $this->m_FSAccount = $p_FSAccount;
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "UPDATE players SET fsaccount = ? WHERE uuid = ?", [
                ["s", $this->m_FSAccount],
                ["s", $this->getPlayer()->getUniqueId()]
            ]
        ));
    }

    //--> MUTE
	/**
	 * @param int|null $p_ExpireSecondFromNow if null is given, player is mute for one month
	 */
	public function setMuted(int $p_ExpireSecondFromNow = null)
	{
		$l_ExpirationTimestamp = time() + (is_null($p_ExpireSecondFromNow) ? 30 * 24 * 60 * 60 : $p_ExpireSecondFromNow);
		$l_Result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
			"UPDATE players SET muted = FROM_UNIXTIME(?) WHERE uuid = ?", [
				["i", $l_ExpirationTimestamp],
				["s", $this->getPlayer()->getUniqueId()]
			]);
		$this->m_MutedTimestamp = $l_ExpirationTimestamp;
	}

	public function isMuted():bool
	{
		return $this->m_MutedTimestamp > time();
	}

	public function getMutedExpiration():int
	{
		return $this->m_MutedTimestamp;
	}
}
