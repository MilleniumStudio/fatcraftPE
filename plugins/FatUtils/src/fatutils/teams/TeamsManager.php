<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 18:36
 */

namespace fatutils\teams;


use fatutils\FatUtils;
use fatutils\spawns\Spawn;
use fatutils\spawns\SpawnManager;
use pocketmine\block\Wool;
use pocketmine\item\Dye;
use pocketmine\Player;
use pocketmine\utils\Color;

class TeamsManager
{
    const CONFIG_KEY_TEAM_ROOT = "teams";
//    const CONFIG_KEY_TEAM_PREFIX = "prefix";
    const CONFIG_KEY_TEAM_MAX_PLAYERS = "maxPlayer";
    const CONFIG_KEY_TEAM_SPAWN = "spawn";
    const CONFIG_KEY_TEAM_COLOR = "color";

    private static $m_Instance = null;
    private $m_Teams = [];

    public static function getInstance(): TeamsManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new TeamsManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        if (!is_null(FatUtils::getInstance()->getTemplateConfig()) && FatUtils::getInstance()->getTemplateConfig()->exists(TeamsManager::CONFIG_KEY_TEAM_ROOT))
        {
            FatUtils::getInstance()->getLogger()->info("TeamManager loading...");
            foreach (FatUtils::getInstance()->getTemplateConfig()->get(TeamsManager::CONFIG_KEY_TEAM_ROOT) as $key => $value)
            {
                $newTeam = new Team();
                if (is_string($key))
                {
                    $newTeam->setName($key);

                    if (gettype($value) === 'array')
                    {
//                        if (array_key_exists(TeamsManager::CONFIG_KEY_TEAM_PREFIX, $value))
//                            $newTeam->setPrefix($value[TeamsManager::CONFIG_KEY_TEAM_PREFIX]);

                        if (array_key_exists(TeamsManager::CONFIG_KEY_TEAM_MAX_PLAYERS, $value) && is_numeric($value[TeamsManager::CONFIG_KEY_TEAM_MAX_PLAYERS]))
                            $newTeam->setMaxPlayer($value[TeamsManager::CONFIG_KEY_TEAM_MAX_PLAYERS]);

                        if (array_key_exists(TeamsManager::CONFIG_KEY_TEAM_COLOR, $value) && is_string($value[TeamsManager::CONFIG_KEY_TEAM_COLOR]))
                            $newTeam->setColor($value[TeamsManager::CONFIG_KEY_TEAM_COLOR]);

                        if (array_key_exists(TeamsManager::CONFIG_KEY_TEAM_SPAWN, $value))
                        {
                            $l_SpawnName = $value[TeamsManager::CONFIG_KEY_TEAM_SPAWN];
                            $l_Spawn = SpawnManager::getInstance()->getSpawnByName($l_SpawnName);
                            if ($l_Spawn instanceof Spawn)
                                $newTeam->setSpawn($l_Spawn);
                        }
                    }

                } else
                    $newTeam->setName($value);

                FatUtils::getInstance()->getLogger()->info("   - " . $newTeam->getName() . " (maxPlayer:" . $newTeam->getMaxPlayer() . ")");
                $this->addTeam($newTeam);
            }
        }
    }

    public function addTeam(Team $p_Team)
    {
        $this->m_Teams[] = $p_Team;
    }

    public function getPlayerTeam(Player $p_Player): ?Team
    {
        foreach ($this->m_Teams as $l_Team)
        {
            if ($l_Team instanceof Team)
            {
                if ($l_Team->isPlayerInTeam($p_Player))
                    return $l_Team;
            }
        }

        return null;
    }

    public function addInBestTeam(Player $p_Player): ?Team
    {
        $l_EmptiestTeam = $this->getEmptiestTeam();
        if (!is_null($l_EmptiestTeam))
            $l_EmptiestTeam->addPlayer($p_Player);
        FatUtils::getInstance()->getLogger()->info($p_Player->getName() . " have been put in " . (isset($l_EmptiestTeam) ? $l_EmptiestTeam->getName() : "no") . " team.");
        return $l_EmptiestTeam;
    }

    public function getEmptiestTeam(): ?Team
    {
        $l_LessLoadedTeam = null;

        foreach ($this->m_Teams as $l_Team)
        {
            if ($l_Team instanceof Team)
            {
                if ((is_null($l_LessLoadedTeam) && $l_Team->getPlaceLeft() > 0) || ($l_LessLoadedTeam instanceof Team && $l_Team->getPlayerCount() < $l_LessLoadedTeam->getPlayerCount()))
                    $l_LessLoadedTeam = $l_Team;
            }
        }

        return $l_LessLoadedTeam;
    }

    /**
     * @return array
     */
    public function getTeams(): array
    {
        return $this->m_Teams;
    }
}