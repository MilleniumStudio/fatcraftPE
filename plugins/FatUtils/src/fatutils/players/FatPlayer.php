<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 10:48
 */

namespace fatutils\players;


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

    /**
     * @return bool
     */
    public function isHealthDisplayed(): bool
    {
        return $this->m_DisplayHealth ?? false || PlayersManager::getInstance()->isHealthDisplayed();
    }

    public function getFormattedNameTag():string
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

        return $this->getPlayer()->getName() . "\n" . $healthBar;
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