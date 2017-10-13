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

    const KEY_SPAWNS = "spawns";
    const KEY_GUN_PART_LOC = "gunPartsLoc";

    public $gunPartsLocs = [];

	/**
	 * MurderConfig constructor.
	 * @param Config $p_Config
	 */
	public function __construct(Config $p_Config)
	{
	    if($p_Config->exists(MurderConfig::KEY_SPAWNS)){
            foreach ($p_Config->getNested(MurderConfig::KEY_SPAWNS) as $spawn) {
                SpawnManager::getInstance()->addSpawn(new Spawn(WorldUtils::stringToLocation($spawn)));
	        }
        }else{
	        echo "pas de spawns ?\n";
        }

        if($p_Config->exists(MurderConfig::KEY_GUN_PART_LOC)){
            foreach ($p_Config->getNested(MurderConfig::KEY_GUN_PART_LOC) as $loc) {
                $this->gunPartsLocs[] = WorldUtils::stringToLocation($loc);
            }
        }else{
            echo "pas de spawns de guns' parts ?\n";
        }
	}
}