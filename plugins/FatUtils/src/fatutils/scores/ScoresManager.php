<?php

namespace fatutils\scores;

use fatutils\gamedata\GameDataManager;
use fatcraft\loadbalancer\LoadBalancer;
use libasynql\result\MysqlResult;
use libasynql\result\MysqlSuccessResult;
use libasynql\DirectQueryMysqlTask;
use fatutils\FatUtils;
use pocketmine\utils\UUID;
use pocketmine\Player;

abstract class ScoresManager
{

    public $m_Positions = array();

    public function __construct()
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
            `varchar` varchar(36) NOT NULL,
            `position` int(11) NOT NULL,
            `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        )");
    }

    public function recordScore(String $p_Player, int $p_Position)
    {
        if (GameDataManager::getInstance()->getGameId() != 0)
        {
            FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
                new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                    "INSERT INTO scores (game, varchar, position) VALUES (?, ?, ?)", [
                    ["i", GameDataManager::getInstance()->getGameId()],
                    ["s", $p_Player],
                    ["i", $p_Position]
                ]
            ));
        }
    }

    // THE SIMPLE WAY
    // games plugin record it own player list
    // the list is reversed and rewards are given:
    // 1 => 100%
    // 2 => 50%
    // 3 => 33%
    // 4 => 25%
    // 5 => 20%
    // 6 => 16%
    // 7 => 14%
    // 8 => 12%
    // 9 => 11%
    // 10 => 10%
    public function giveRewards()
    {
        FatUtils::getInstance()->getLogger()->info("Giving rewards :");
        $l_ReversePositions = array_reverse($this->m_Positions);
        var_dump($l_ReversePositions);

        // record ordered players
        GameDataManager::getInstance()->recordBoard($l_ReversePositions);

        // get max rewards from config
        $l_Rewards = FatUtils::getInstance()->getTemplateConfig()->get("rewards");

        // define base divider
        $l_Divider = 1;

        // reverse order & iter
        for ($i = 0; $i <= count($l_ReversePositions); $i++)
        {
            $l_PlayerName = $l_ReversePositions[$i];

            $this->recordScore($l_PlayerName, $i);

            // get player instance
            $p_Player = FatUtils::getInstance()->getServer()->getPlayer($l_PlayerName);

            // calculate rewards (based on reverse death order)
            $l_Money = round($l_Rewards["money"] / $l_Divider);
            $l_XP = round($l_Rewards["money"] / $l_Divider);
            FatUtils::getInstance()->getLogger()->info("Player " . $l_PlayerName . " win " . $l_Money . " money");
            FatUtils::getInstance()->getLogger()->info("Player " . $l_PlayerName . " win " . $l_XP . " XP");

            // check if player is online (no reward if offline)
            if ($p_Player != null)
            {
                // add general stats
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("Money", $p_Player, $l_Money);
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("XP", $p_Player, $l_XP);

                $p_Player->sendMessage(str_replace("{0}", $l_Money, HungerGame::getInstance()->getConfig()->get("endgameMessageMoney")));
                $p_Player->sendMessage(str_replace("{0}", $l_XP, HungerGame::getInstance()->getConfig()->get("endgameMessageXP")));

                // add game specific stats
                $l_ServerType = \fatcraft\loadbalancer\LoadBalancer::getInstance()->getServerType();
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_XP", $p_Player, $l_XP);
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_played", $p_Player, 1);

                // increment divider
                $l_Divider++;
            }
            else
            {
                FatUtils::getInstance()->getLogger()->info("Player " . $l_PlayerName . " not connected, not recieve " . $l_Money . " money & " . $l_XP . " XP.");
            }
        }
    }
}
