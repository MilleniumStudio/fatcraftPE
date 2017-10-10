<?php

namespace fatutils\scores;

use fatutils\gamedata\GameDataManager;
use fatcraft\loadbalancer\LoadBalancer;
use fatutils\players\PlayersManager;
use fatutils\tools\TextFormatter;
use libasynql\DirectQueryMysqlTask;
use fatutils\FatUtils;
use pocketmine\Player;

abstract class ScoresManager
{
    protected static $m_Instance = null;

    protected $m_Positions = [];

    protected function __construct()
    {
        $this->initialize();
    }

    private function initialize()
    {
        $this->initDatabase();
    }

    private function initDatabase()
    {
        LoadBalancer::getInstance()->connectMainThreadMysql()->query("CREATE TABLE IF NOT EXISTS `scores` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `game` int(11) NOT NULL,
            `player` varchar(36) NOT NULL,
            `position` int(11) NOT NULL,
            `data` text DEFAULT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        )");
    }

    protected function recordScore(String $p_Player, int $p_Position, $data = array())
    {
        if (GameDataManager::getInstance()->getGameId() != 0)
        {
            FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
                new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                    "INSERT INTO scores (game, player, position, data) VALUES (?, ?, ?, ?)", [
                    ["i", GameDataManager::getInstance()->getGameId()],
                    ["s", $p_Player],
                    ["i", $p_Position],
                    ["s", json_encode($data)]
                ]
            ));
        }
    }

    public abstract function giveRewards();
}
