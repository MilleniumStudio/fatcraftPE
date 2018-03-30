<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 14:17
 */

namespace battleroyal;


use fatutils\players\PlayersManager;
use fatutils\tools\WorldUtils;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;

class BattleRoyalConfig
{
    const CONFIG_KEY_START_GAME_LOC = "startGameLocation";
    const CONFIG_KEY_WAITING_GAME_LOC = "waitingGameLoc";


    private $m_StartGamePosition = null;
    private $m_WaitingPosition = null;

    public $Pos1 = null;
    public $Radius1 = 0;

    /**
	 * HungerGameConfig constructor.
	 * @param Config $p_Config
	 */
	public function __construct(Config $p_Config)
	{
        $this->m_StartGamePosition = WorldUtils::stringToLocation($p_Config->get(BattleRoyalConfig::CONFIG_KEY_START_GAME_LOC, ""));

        if ($p_Config->exists(BattleRoyalConfig::CONFIG_KEY_WAITING_GAME_LOC))
            $this->m_WaitingPosition = WorldUtils::stringToLocation($p_Config->get(BattleRoyalConfig::CONFIG_KEY_WAITING_GAME_LOC, ""));
        else
            $this->m_WaitingPosition = BattleRoyal::getInstance()->getServer()->getLevel(1)->getSpawnLocation();

        $x = rand(-620, -430);
        $y = 95;
        $z = rand (570 , 800);
        // legacy coord : new Vector3(-497, 115, 743);
        $this->Pos1 = new Vector3($x, $y, $z);
        $this->Radius1 = 200;

    }

    public function getWaitingLocation() : Position
    {
        return $this->m_WaitingPosition;
    }

    public function getStartGameLocation() : Position
    {
        return $this->m_StartGamePosition;
    }

    public function getPos1() : Vector3
    {
        return $this->Pos1;
    }

    public function getRadius1() : int
    {
        return $this->Radius1;
    }

    public function getPos2() : Vector3
    {
        return $this->Pos2;
    }

    public function getRadius2() : int
    {
        return $this->Radius2;
    }


}