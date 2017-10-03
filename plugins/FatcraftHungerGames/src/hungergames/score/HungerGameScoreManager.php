<?php

namespace hungergames\score;

use hungergames\HungerGame;
use pocketmine\Player;
use fatutils\scores\ScoresManager;

class HungerGameScoreManager extends ScoresManager
{

    private static $m_Instance = null;

    private $m_Index = 0;

    public static function getInstance(): HungerGameScoreManager
    {
        if (is_null(self::$m_Instance)) self::$m_Instance = new HungerGameScoreManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        parent::__construct();
    }

    public function registerDeath(Player $p_Player)
    {
        HungerGame::getInstance()->getLogger()->info("Register player DEATH " . $p_Player->getName() . " position " . count($this->m_Positions));
        $this->m_Positions[] = $p_Player->getName();
    }
}