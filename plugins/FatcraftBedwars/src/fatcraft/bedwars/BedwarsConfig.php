<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 14:17
 */

namespace fatcraft\bedwars;


use fatutils\players\PlayersManager;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use fatutils\tools\WorldUtils;
use pocketmine\level\Location;
use pocketmine\utils\Config;

class BedwarsConfig
{
    const CONFIG_KEY_IS_SKYWARS = "isSkywar";
    const CONFIG_KEY_DEATH_ARENA_LOC = "deathArenaLoc";

	private $m_IsSkyWars = false;
	private $m_DeathArenaLoc = null;
	private $m_bedsLocations = [];

	/**
	 * HungerGameConfig constructor.
	 * @param Config $p_Config
	 */
	public function __construct(Config $p_Config)
	{
		$this->m_IsSkyWars = $p_Config->get(BedwarsConfig::CONFIG_KEY_IS_SKYWARS, $this->m_IsSkyWars);

        if ($p_Config->exists(BedwarsConfig::CONFIG_KEY_DEATH_ARENA_LOC))
            $this->m_DeathArenaLoc = WorldUtils::stringToLocation($p_Config->get(BedwarsConfig::CONFIG_KEY_DEATH_ARENA_LOC, ""));
		else
            $this->m_DeathArenaLoc = Bedwars::getInstance()->getServer()->getLevel(1)->getSpawnLocation();

		/** @var Team $team */
		foreach (TeamsManager::getInstance()->getTeams() as $team){
		    $this->m_bedsLocations[$team->getName()] = WorldUtils::stringToLocation($p_Config->getNested("beds.".$team->getName(), ""));
        }
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

    public function getBedLocation(Team $team) : Location{
        return $this->m_bedsLocations[$team->getName()];
    }
}