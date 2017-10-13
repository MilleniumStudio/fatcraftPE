<?php

namespace fatutils\scores;

use fatutils\gamedata\GameDataManager;
use fatcraft\loadbalancer\LoadBalancer;
use fatutils\players\PlayersManager;
use fatutils\tools\TextFormatter;
use libasynql\DirectQueryMysqlTask;
use fatutils\FatUtils;
use pocketmine\Player;

class PlayerScoresManager extends ScoresManager
{
    public static function getInstance():PlayerScoresManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new PlayerScoresManager();
        return self::$m_Instance;
    }

    public function registerPlayer(Player $p_Player)
    {
        FatUtils::getInstance()->getLogger()->info("Register player score: " . $p_Player->getName() . " position " . count($this->m_Positions));
        $data = array();
        $data['name'] = $p_Player->getName();
        $data['uuid'] = $p_Player->getXuid();
        $this->m_Positions[] = $data;
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
    // n => 1/n %
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
            $l_PlayerName = $l_ReversePositions[$i]['name'];
            $l_UUID = $l_ReversePositions[$i]['uuid'];

            // get player instance
            $l_Player = FatUtils::getInstance()->getServer()->getPlayer($l_PlayerName);

            // calculate rewards (based on reverse death order)
            $l_Money = round($l_Rewards["money"] / $l_Divider);
            $l_XP = round($l_Rewards["xp"] / $l_Divider);

            $l_PlayerData = PlayersManager::getInstance()->getFatPlayer($l_Player)->getDatas();
            $l_PlayerData['money'] = $l_Money;
            $l_PlayerData['xp'] = $l_XP;
            $this->recordScore($l_UUID, $i, $l_PlayerData);

            FatUtils::getInstance()->getLogger()->info("Player " . $l_PlayerName . " win " . $l_Money . " money");
            FatUtils::getInstance()->getLogger()->info("Player " . $l_PlayerName . " win " . $l_XP . " XP");

            // check if player is online (no reward if offline)
            if ($l_Player != null)
            {
                // add general stats
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("Money", $l_Player, $l_Money);
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("XP", $l_Player, $l_XP);

                $l_Player->sendMessage((new TextFormatter("reward.endGame.money", ["amount" => $l_Money]))->asStringForPlayer($l_Player));
                $l_Player->sendMessage((new TextFormatter("reward.endGame.xp", ["amount" => $l_XP]))->asStringForPlayer($l_Player));

                // add game specific stats
                $l_ServerType = \fatcraft\loadbalancer\LoadBalancer::getInstance()->getServerType();
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_XP", $l_Player, $l_XP);
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_played", $l_Player, 1);

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
