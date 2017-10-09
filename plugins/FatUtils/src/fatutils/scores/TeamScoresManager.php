<?php

namespace fatutils\scores;

use fatutils\gamedata\GameDataManager;
use fatcraft\loadbalancer\LoadBalancer;
use fatutils\players\PlayersManager;
use fatutils\teams\Team;
use fatutils\tools\TextFormatter;
use libasynql\DirectQueryMysqlTask;
use fatutils\FatUtils;
use pocketmine\Player;

class TeamScoresManager extends ScoresManager
{
    public static function getInstance():TeamScoresManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new TeamScoresManager();
        return self::$m_Instance;
    }

    public function registerTeam(Team $p_Team)
    {
        FatUtils::getInstance()->getLogger()->info("Register player score: " . $p_Team->getName() . " position " . count($this->m_Positions));
        $this->m_Positions[] = $p_Team;
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
//        var_dump($l_ReversePositions);

        // record ordered players
        GameDataManager::getInstance()->recordBoard($l_ReversePositions);

        // get max rewards from config
        $l_Rewards = FatUtils::getInstance()->getTemplateConfig()->get("rewards");

        // define base divider
        $l_Divider = 1;

        // reverse order & iter
        for ($i = 0; $i < count($l_ReversePositions); $i++)
        {
            $l_Team = $l_ReversePositions[$i];
            if ($l_Team instanceof Team)
            {
                foreach ($l_Team->getPlayerUUIDs() as $l_PlayerUUID)
                {
                    $l_FatPlayer = PlayersManager::getInstance()->getFatPlayerByUUID($l_PlayerUUID);
                    // calculate rewards (based on reverse death order)
                    $l_Money = round($l_Rewards["money"] / $l_Divider);
                    $l_XP = round($l_Rewards["xp"] / $l_Divider);

                    $l_PlayerData = $l_FatPlayer->getDatas();
                    $l_PlayerData['money'] = $l_Money;
                    $l_PlayerData['xp'] = $l_XP;
                    $this->recordScore($l_PlayerUUID, $i, $l_PlayerData);

                    FatUtils::getInstance()->getLogger()->info("Player " . $l_FatPlayer->getName() . " win " . $l_Money . " money");
                    FatUtils::getInstance()->getLogger()->info("Player " . $l_FatPlayer->getName() . " win " . $l_XP . " XP");

                    // check if player is online (no reward if offline)
                    if ($l_FatPlayer->getPlayer()->isOnline())
                    {
                        $l_Player = $l_FatPlayer->getPlayer();
                        // add general stats
                        \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("Money", $l_Player, $l_Money);
                        \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("XP", $l_Player, $l_XP);

                        $l_Player->sendMessage((new TextFormatter("reward.endGame.money", ["amount" => $l_Money]))->asStringForFatPlayer($l_FatPlayer));
                        $l_Player->sendMessage((new TextFormatter("reward.endGame.xp", ["amount" => $l_XP]))->asStringForFatPlayer($l_FatPlayer));

                        // add game specific stats
                        $l_ServerType = \fatcraft\loadbalancer\LoadBalancer::getInstance()->getServerType();
                        \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_XP", $l_Player, $l_XP);
                        \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_played", $l_Player, 1);

                        // increment divider
                    }
                }
                $l_Divider++;
            }
        }
    }
}
