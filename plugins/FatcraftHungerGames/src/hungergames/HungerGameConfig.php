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
	const CONFIG_KEY_DEATH_ARENA_LOC_TYPE = "deathArenaLocType";
	const CONFIG_KEY_DEATH_ARENA_RADIUS = "deathArenaLocRadius";

	const DEATH_ARENA_TYPE_SURFACE = "deathArenaTypeSurface";
	const DEATH_ARENA_TYPE_PERIMETER = "deathArenaTypePerimeter";


	private $m_IsSkyWars = false;
	private $m_DeathArenaLoc = null;

	private $m_DeathArenaLocType = null;
	private $m_DeathArenaLocRadius = null;

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

		if ($p_Config->exists(HungerGameConfig::CONFIG_KEY_DEATH_ARENA_RADIUS))
			$this->m_DeathArenaLocRadius = $p_Config->get(HungerGameConfig::CONFIG_KEY_DEATH_ARENA_RADIUS, "");
		else
			$this->m_DeathArenaLocRadius = 3;

		if ($p_Config->exists(HungerGameConfig::CONFIG_KEY_DEATH_ARENA_LOC_TYPE))
			$this->m_DeathArenaLocType = WorldUtils::stringToLocation($p_Config->get(HungerGameConfig::CONFIG_KEY_DEATH_ARENA_LOC_TYPE, ""));
		if ($this->m_DeathArenaLocType != HungerGameConfig::DEATH_ARENA_TYPE_SURFACE && $this->m_DeathArenaLocType != HungerGameConfig::DEATH_ARENA_TYPE_PERIMETER)
			$this->m_DeathArenaLocType = HungerGameConfig::DEATH_ARENA_TYPE_SURFACE;
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

	/**
	 * @return string
	 */
	public function getMDeathArenaLocType():string
	{
		return $this->m_DeathArenaLocType;
	}

	/**
	 * @return float
	 */
	public function getMDeathArenaLocRadius():int
	{
		var_dump($this->m_DeathArenaLocRadius);
		return $this->m_DeathArenaLocRadius;
	}
}