<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 10:48
 */

namespace fatutils\players;

use fatutils\spawns\Spawn;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use fatutils\tools\TextFormatter;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FatPlayer
{
    const PLAYER_STATE_WAITING = 0;
    const PLAYER_STATE_PLAYING = 1;

	private $m_Player;
	private $m_State = 0;
	private $m_HasLost = false;
	private $m_DisplayHealth = null;
	private $m_Data = [];

	private $m_Spawn = null;

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

    public function getLanguage():int
    {
        //TODO language info storage
        return TextFormatter::LANG_ID_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isHealthDisplayed(): bool
    {
        return $this->m_DisplayHealth ?? false || PlayersManager::getInstance()->isHealthDisplayed();
    }

    public function getFormattedNameTag():string
    {
        $healthBar = "";
        if ($this->isHealthDisplayed())
        {
            $healthBar = "[";
            $playerHealth = $this->getPlayer()->getHealth() * 10 / $this->getPlayer()->getMaxHealth();
            for ($i = 0; $i < 10; $i++)
            {
                if ($playerHealth > 0)
                {
                    $healthBar .= TextFormat::RED . "█";
                    $playerHealth--;
                } else
                    $healthBar .= " ";
            }
            $healthBar .= TextFormat::RESET . "]";
        }

        $l_Team =TeamsManager::getInstance()->getPlayerTeam($this->getPlayer());
        return (isset($l_Team) ? $l_Team->getPrefix() : "") . $this->getPlayer()->getName() . "\n" . $healthBar;
    }

	/**
	 * @return Player
	 */
	public function getPlayer(): Player
	{
		return $this->m_Player;
	}

    public function updateFormattedNameTag()
    {
        $this->getPlayer()->setNameTag($this->getFormattedNameTag());
    }
}