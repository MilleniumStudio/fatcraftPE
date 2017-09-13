<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 14:17
 */

namespace hungergames;


use fatutils\players\PlayersManager;
use fatutils\tools\WorldUtils;
use pocketmine\utils\Config;

class HungerGameConfig
{
    const CONFIG_KEY_IS_SKYWARS = "isSkywar";
    const CONFIG_KEY_DEATH_ARENA_LOC = "deathArenaLoc";

	private $m_IsSkyWars = false;
	private $m_DeathArenaLoc = null;

	/**
	 * HungerGameConfig constructor.
	 * @param Config $p_Config
	 */
	public function __construct(Config $p_Config)
	{
		$this->m_IsSkyWars = $p_Config->get(HungerGameConfig::CONFIG_KEY_IS_SKYWARS, $this->m_IsSkyWars);

        if ($p_Config->exists(HungerGameConfig::CONFIG_KEY_DEATH_ARENA_LOC))
            $this->m_DeathArenaLoc = WorldUtils::stringToLocation($p_Config->get(HungerGameConfig::CONFIG_KEY_DEATH_ARENA_LOC, ""));
		else
            $this->m_DeathArenaLoc = HungerGame::getInstance()->getServer()->getLevel(1)->getSpawnLocation();
	}

	/**
	 * @return bool
	 */
	public function isSkyWars():bool
	{
		return $this->m_IsSkyWars;
	}

    /**
     * @return null|\pocketmine\level\Position
     */
    public function getDeathArenaLoc()
    {
        return $this->m_DeathArenaLoc;
    }
}