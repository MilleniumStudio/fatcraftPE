<?php

namespace fatutils\gamedata;

use fatcraft\loadbalancer\LoadBalancer;
use libasynql\result\MysqlResult;
use libasynql\result\MysqlSuccessResult;
use libasynql\DirectQueryMysqlTask;
use fatutils\FatUtils;
use pocketmine\utils\UUID;

class GameDataManager
{

    private static $m_Instance = null;
    private $m_Mysql;
    private $m_GameType;
    private $m_GameId;

    //Game data events types :
    const START = "start";
    const JOIN = "join";
    const LEAVE = "leave";
    const KILL = "kill";
    const DEATH = "death";
    const WIN = "win";
    const BOARD = "board";
    const END = "end";

    public static function getInstance(): GameDataManager
    {
        if (is_null(self::$m_Instance)) self::$m_Instance = new GameDataManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        $this->m_Mysql = LoadBalancer::getInstance()->connectMainThreadMysql();
        $this->initDatabase();
        $this->m_GameType = LoadBalancer::getInstance()->getServerType();
        $this->m_GameId = $this->newGame($this->m_GameType);
        if ($this->m_GameId == 0)
        {
            FatUtils::getInstance()->getLogger()->critical("Game ID = 0, game data will not be saved !");
        }
    }

    private function initDatabase()
    {
        $this->m_Mysql->query("CREATE TABLE IF NOT EXISTS `games` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type` varchar(10) NOT NULL,
            `launch` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `start` timestamp NULL DEFAULT NULL,
            `end` timestamp NULL DEFAULT NULL,
            `end_cause` varchar(100) DEFAULT NULL,
            PRIMARY KEY (`id`)
        )");

        $this->m_Mysql->query("CREATE TABLE `games_data` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `game_id` int(11) NOT NULL,
            `event` varchar(20) NOT NULL,
            `player` varchar(36) DEFAULT NULL,
            `data` text,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        )");
    }

    public function newGame(String $p_Type): int
    {
        $result = MysqlResult::executeQuery($this->m_Mysql, "INSERT INTO games (type) VALUES (?)", [
                    ["s", $p_Type]
        ]);
        if (($result instanceof MysqlSuccessResult))
        {
            return $result->insertId;
        }
        return 0;
    }

    public function recordStartGame()
    {
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "UPDATE games SET end = CURRENT_TIMESTAMP, start = ? WHERE id = ?", [
                ["i", $this->m_GameId]
            ]
        ));
    }

    public function recordStopGame(String $p_Cause)
    {
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "UPDATE games SET end = CURRENT_TIMESTAMP, end_cause = ? WHERE id = ?", [
                ["s", $p_Cause],
                ["i", $this->m_GameId]
            ]
        ));
    }

    public function recordJoin(UUID $p_Player, String $p_IP)
    {
        $data['ip'] = $p_IP;
        $this->insertGameData(GameDataManager::JOIN, $p_Player->toString(), json_encode($data));
    }

    public function recordLeave(UUID $p_Player)
    {
        $this->insertGameData(GameDataManager::LEAVE, $p_Player->toString(), "");
    }

    public function recordKill(UUID $p_Player, String $p_Killed)
    {
        $data['target'] = $p_Killed;
        $this->insertGameData(GameDataManager::KILL, $p_Player->toString(), json_encode($data));
    }

    public function recordDeath(UUID $p_Player, String $p_By)
    {
        $data['by'] = $p_By;
        $this->insertGameData(GameDataManager::DEATH, $p_Player->toString(), json_encode($data));
    }

    public function recordBoard($p_Data = array())
    {
        $this->insertGameData(GameDataManager::BOARD, null, json_encode($p_Data));
    }

    public function recordWin(UUID $p_Player, array $p_Rewards)
    {
        $data['rewards'] = $p_Rewards;
        $this->insertGameData(GameDataManager::WIN, $p_Player->toString(), json_encode($data));
    }

    public function recordWinTeam(array $p_Players, String $p_Team, array $p_Rewards)
    {
        $data['team'] = $p_Team;
        $data['rewards'] = $p_Rewards;
        foreach ($p_Players as $l_Player)
        {
            $this->insertGameData(GameDataManager::WIN, $l_Player->toString(), json_encode($data));
        }
    }

    private function insertGameData(String $p_EventType, String $p_Player, $p_Data)
    {
        if ($this->m_GameId != 0)
        {
            FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
                new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                    "INSERT INTO games_data (game_id, event, player, data) VALUES (?, ?, ?, ?)", [
                    ["i", $this->m_GameId],
                    ["s", $p_EventType],
                    ["s", $p_Player],
                    ["s", $p_Data]
                ]
            ));
        }
    }
}
