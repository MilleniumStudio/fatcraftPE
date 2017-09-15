<?php

namespace hungergames\score;

use hungergames\HungerGame;
use pocketmine\Player;

class HungerGameScoreManager
{

    private static $m_Instance = null;

    private $m_Index = 0;
    private $m_DeathOrder = array();

    public static function getInstance(): HungerGameScoreManager
    {
        if (is_null(self::$m_Instance)) self::$m_Instance = new HungerGameScoreManager();
        return self::$m_Instance;
    }

    private function __construct()
    {

    }

    public function registerDeath(Player $p_Player)
    {
        HungerGame::getInstance()->getLogger()->info("Register player DEATH " . $p_Player->getName() . " position " . $this->m_Index);
        $this->m_DeathOrder[$this->m_Index] = $p_Player->getName();
        $this->m_Index++;
    }

    public function giveRewards()
    {
        // get max rewards from config
        $l_Rewards = HungerGame::getInstance()->getConfig()->get("rewards");

        // define base divider
        $l_Divider = 1;

        // reverse death order & iter
        foreach (array_reverse($this->m_DeathOrder) as  $l_PlayerName)
        {
            // get player instance
            $p_Player = HungerGame::getInstance()->getServer()->getPlayer($l_PlayerName);

            // calculate rewards (based on reverse death order)
            $l_Money = round($l_Rewards["money"] / $l_Divider);
            $l_XP = round($l_Rewards["money"] / $l_Divider);
            HungerGame::getInstance()->getLogger()->info("Player " . $l_PlayerName . " win " . $l_Money . " money");
            HungerGame::getInstance()->getLogger()->info("Player " . $l_PlayerName . " win " . $l_XP . " XP");

            // check if player is online (no reward if offline)
            if ($p_Player != null)
            {
                // add general stats
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("Money", $p_Player, $l_Money);
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("XP", $p_Player, $l_XP);

                // add game specific stats
                $l_ServerType = \fatcraft\loadbalancer\LoadBalancer::getInstance()->getServerType();
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_XP", $p_Player, $l_XP);
                \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_played", $p_Player, 1);

                // increment divider
                $l_Divider++;
            }
            else
            {
                HungerGame::getInstance()->getLogger()->info("Player " . $l_PlayerName . " not connected, not recieve " . $l_Money . " money & " . $l_XP . " XP.");
            }
        }
    }
}