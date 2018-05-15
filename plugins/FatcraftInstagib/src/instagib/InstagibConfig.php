<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 5/11/18
 * Time: 5:59 PM
 */

use fatutils\spawns\Spawn;
use fatutils\spawns\SpawnManager;
use fatutils\tools\WorldUtils;
use pocketmine\utils\Config;

class InstagibConfig
{
    const END_GAME_TIMER = "endGameTime";
    const KEY_SPAWNS = "spawns";

    private  $m_endGameTimer = 0;

    public function __construct(Config $p_config)
    {
        if ($p_config->exists(InstagibConfig::END_GAME_TIMER))
            $this->m_endGameTimer = $p_config->get(InstagibConfig::END_GAME_TIMER, 0);
        else
            echo("endGameTime property does not exist in the config.yml\n");

        if($p_config->exists(InstagibConfig::KEY_SPAWNS)){
            foreach ($p_config->getNested(InstagibConfig::KEY_SPAWNS) as $spawn) {
                var_dump($spawn);
                SpawnManager::getInstance()->addSpawn(new Spawn(WorldUtils::stringToLocation($spawn)));
            }
        }else{
            echo "pas de spawns ?\n";
        }


    }

    public function getEndGameTime() : int
    {
        return $this->m_endGameTimer;
    }
}
