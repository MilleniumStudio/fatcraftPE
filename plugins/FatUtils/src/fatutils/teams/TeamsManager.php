<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 18:36
 */

namespace fatutils\teams;


use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\spawns\Spawn;
use fatutils\spawns\SpawnManager;
use fatutils\tools\ClickableNPC;
use fatutils\tools\TextFormatter;
use fatutils\tools\WorldUtils;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use pocketmine\block\Wool;
use pocketmine\item\Dye;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Color;
use pocketmine\utils\UUID;

class TeamsManager
{
    const CONFIG_KEY_TEAM_ROOT = "teams";
//    const CONFIG_KEY_TEAM_PREFIX = "prefix";
    const CONFIG_KEY_TEAM_MAX_PLAYERS = "maxPlayer";
    const CONFIG_KEY_TEAM_SPAWN = "spawn";
    const CONFIG_KEY_TEAM_COLOR = "color";
    const CONFIG_KEY_NPC_TEAM_SELECTOR = "npcTeamSelector";

    private static $m_Instance = null;
    private $m_Teams = [];
    private $m_NPCSelectors = [];

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
        if (!is_null(FatUtils::getInstance()->getTemplateConfig()) && FatUtils::getInstance()->getTemplateConfig()->exists(TeamsManager::CONFIG_KEY_TEAM_ROOT)) {
            FatUtils::getInstance()->getLogger()->info("TeamManager loading...");
            foreach (FatUtils::getInstance()->getTemplateConfig()->get(TeamsManager::CONFIG_KEY_TEAM_ROOT) as $key => $value) {
                $newTeam = new Team();
                if (is_string($key)) {
                    $newTeam->setName($key);

                    if (gettype($value) === 'array') {
//                        if (array_key_exists(TeamsManager::CONFIG_KEY_TEAM_PREFIX, $value))
//                            $newTeam->setPrefix($value[TeamsManager::CONFIG_KEY_TEAM_PREFIX]);

                        if (array_key_exists(TeamsManager::CONFIG_KEY_TEAM_MAX_PLAYERS, $value) && is_numeric($value[TeamsManager::CONFIG_KEY_TEAM_MAX_PLAYERS]))
                            $newTeam->setMaxPlayer($value[TeamsManager::CONFIG_KEY_TEAM_MAX_PLAYERS]);

                        if (array_key_exists(TeamsManager::CONFIG_KEY_TEAM_COLOR, $value) && is_string($value[TeamsManager::CONFIG_KEY_TEAM_COLOR]))
                            $newTeam->setColor($value[TeamsManager::CONFIG_KEY_TEAM_COLOR]);

                        if (array_key_exists(TeamsManager::CONFIG_KEY_TEAM_SPAWN, $value)) {
                            $l_SpawnName = $value[TeamsManager::CONFIG_KEY_TEAM_SPAWN];
                            $l_Spawn = SpawnManager::getInstance()->getSpawnByName($l_SpawnName);
                            if ($l_Spawn instanceof Spawn)
                                $newTeam->setSpawn($l_Spawn);
                        }
                    }

                } else
                    $newTeam->setName($value);

                FatUtils::getInstance()->getLogger()->info("   - " . $newTeam->getColoredName() . " (maxPlayer:" . $newTeam->getMaxPlayer() . ")");
                $this->addTeam($newTeam);
            }
            //load NPCs
            foreach (FatUtils::getInstance()->getTemplateConfig()->get(TeamsManager::CONFIG_KEY_NPC_TEAM_SELECTOR) as $key => $value){
                $this->addNPC(WorldUtils::stringToLocation($value));
            }
        }
    }

    public function addTeam(Team $p_Team)
    {
        $this->m_Teams[] = $p_Team;
    }

    public function getPlayerTeam(Player $p_Player): ?Team
    {
        foreach ($this->m_Teams as $l_Team) {
            if ($l_Team instanceof Team) {
                if ($l_Team->isPlayerInTeam($p_Player))
                    return $l_Team;
            }
        }

        return null;
    }

    public function getAliveTeamNbr():int
    {
        return count($this->getAliveTeams());
    }

    public function getAliveTeams():array
    {
        $l_Ret = [];
        foreach ($this->m_Teams as $l_Team)
        {
            if ($l_Team instanceof Team)
            {
                $l_TeamAlivePlayers = $l_Team->getAlivePlayers();
                if (count($l_TeamAlivePlayers) > 0)
                    $l_Ret[] = $l_Team;
            }
        }
        return $l_Ret;
    }

    public function addInBestTeam(Player $p_Player): ?Team
    {
        $l_EmptiestTeam = $this->getEmptiestTeam();
        if (!is_null($l_EmptiestTeam))
            $this->addInTeam($p_Player, $l_EmptiestTeam);
//            $l_EmptiestTeam->addPlayer($p_Player);
//        FatUtils::getInstance()->getLogger()->info($p_Player->getName() . " have been put in " . (isset($l_EmptiestTeam) ? $l_EmptiestTeam->getName() : "no") . " team.");
        return $l_EmptiestTeam;
    }

    public function addInBestTeamByUUID(string $p_PlayerBinUUID): ?Team
    {
        return $this->addInBestTeam(PlayersManager::getInstance()->getPlayerFromUUID(UUID::fromString($p_PlayerBinUUID)));
    }

    public function addInTeam(Player $p_Player, Team $p_team)
    {
        $team = $this->getPlayerTeam($p_Player);
        if ($team != null) {
            $team->removePlayer($p_Player);
        }
        $p_team->addPlayer($p_Player);
        Server::getInstance()->broadcastMessage($p_Player->getName() . " join team " . $p_team->getColoredName());
    }

    public function getEmptiestTeam(): ?Team
    {
        $l_LessLoadedTeam = null;

        foreach ($this->m_Teams as $l_Team) {
            if ($l_Team instanceof Team) {
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

	public function getTeamByName(string $p_Name): ?Team
	{
		foreach ($this->m_Teams as $l_Team)
		{
			if ($l_Team instanceof Team)
			{
				if ($l_Team->getName() === $p_Name)
					return $l_Team;
			}
		}
		return null;
	}

    public function displayTeamSelection(Player $p_player)
    {
        $l_Window = new ButtonWindow($p_player);
        $l_Window->setTitle((new TextFormatter("team.choice"))->asStringForPlayer($p_player));
        /** @var Team $team */
        foreach ($this->getTeams() as $team) {
            $playerList = " (" . implode(", ", $team->getPlayersName()) . ")";
            $l_Window->addPart((new Button())
                ->setText($team->getColoredName() . $playerList)
                ->setCallback(function () use (&$p_player, $team) {
                    $this->addInTeam($p_player, $team);
                })
            );
        }
        $l_Window->open();
    }

    public function balanceTeams()
    {
        // find the ~number of player per teams
        $maxPlayerPerTeam = ceil((float)(count(PlayersManager::getInstance()->getAlivePlayers())) / (float)(count($this->m_Teams)));
        /** @var Team $team */
        foreach ($this->m_Teams as $team) {
            while($team->getPlayerCount() > $maxPlayerPerTeam)
            {
                $playerBinUUID = array_pop($team->m_Players);
                $newTeam = $this->addInBestTeamByUUID($playerBinUUID);
                echo "switch ".PlayersManager::getInstance()->getPlayerFromUUID(UUID::fromString($playerBinUUID))->getName()." from ".$team->getName()." to ".$newTeam->getName()."\n";
            }
        }
    }

    //-----------------
    // NPC Selectors
    //-----------------

    public function addNPC(Location $p_location)
    {
        $npc = new ClickableNPC($p_location);
        $this->m_NPCSelectors[] = $npc;
        $npc->setVisibleName((new TextFormatter("team.choice"))->asString());
        $npc->setOnHitCallback(function ($player) {
            if ($player instanceof Player)
                $this->displayTeamSelection($player);
        });
    }

    public function clearNPCs(){
        /** @var ClickableNPC $npc */
        foreach ($this->m_NPCSelectors as $npc) {
            $npc->villager->kill();
        }
        $this->m_NPCSelectors = [];
    }

}