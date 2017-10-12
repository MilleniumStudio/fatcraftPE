<?php
/**
 * Created by Unikaz.
 */

namespace fatcraft\murder;


use fatutils\spawns\Spawn;
use fatutils\spawns\SpawnManager;
use fatutils\tools\WorldUtils;
use pocketmine\utils\Config;

class MurderConfig
{

    private $spawns = [];

	/**
	 * MurderConfig constructor.
	 * @param Config $p_Config
	 */
	public function __construct(Config $p_Config)
	{
	    if($p_Config->exists("spawns")){
            foreach ($p_Config->getNested("spawns") as $spawn) {
                SpawnManager::getInstance()->addSpawn(new Spawn(WorldUtils::stringToLocation($spawn)));
	        }
        }else{
	        echo "pas de spawns ?\n";
        }
	}
}