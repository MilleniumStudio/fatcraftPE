<?php
/**
 * Created by PhpStorm.
 * User: Nyhven
 * Date: 07/09/2017
 * Time: 15:45
 */

namespace fatutils\spawns;

use fatutils\FatUtils;
use fatutils\tools\ItemUtils;
use fatutils\tools\WorldUtils;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\level\Location;

class SpawnManager
{
    const CONFIG_KEY_SPAWN_ROOT = "spawns";
    const CONFIG_KEY_SPAWN_LOCATION = "location";
    const CONFIG_KEY_SPAWN_BLOCK_TYPE = "blockType";
    const CONFIG_KEY_SPAWN_BARRIER_TYPE = "barrierType";

    private static $m_Instance = null;
    private $m_Spawns = [];

    public static function getInstance(): SpawnManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new SpawnManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        if (!is_null(FatUtils::getInstance()->getTemplateConfig()) && FatUtils::getInstance()->getTemplateConfig()->exists(SpawnManager::CONFIG_KEY_SPAWN_ROOT))
        {
            FatUtils::getInstance()->getLogger()->info("SpawnManager loading...");
            foreach (FatUtils::getInstance()->getTemplateConfig()->get(SpawnManager::CONFIG_KEY_SPAWN_ROOT) as $l_SpawnName => $l_SpawnConf)
            {
                if (array_key_exists(SpawnManager::CONFIG_KEY_SPAWN_LOCATION, $l_SpawnConf))
                {
                    $l_NewSpawn = new Spawn(WorldUtils::stringToLocation($l_SpawnConf[SpawnManager::CONFIG_KEY_SPAWN_LOCATION]));
                    $l_NewSpawn->setName($l_SpawnName);

                    if (array_key_exists(SpawnManager::CONFIG_KEY_SPAWN_BLOCK_TYPE, $l_SpawnConf))
                        $l_NewSpawn->setBlockType(ItemUtils::getItemIdFromName($l_SpawnConf[SpawnManager::CONFIG_KEY_SPAWN_BLOCK_TYPE]));

                    if (array_key_exists(SpawnManager::CONFIG_KEY_SPAWN_BARRIER_TYPE, $l_SpawnConf))
                        $l_NewSpawn->setBarrierType(ItemUtils::getItemIdFromName($l_SpawnConf[SpawnManager::CONFIG_KEY_SPAWN_BARRIER_TYPE]));

                    FatUtils::getInstance()->getLogger()->info("   - " . $l_NewSpawn->getLocation());
                    $this->addSpawn($l_NewSpawn);
                }
            }
        }
    }

    //----------------
    // UTILS
    //----------------
    public function addSpawn(Spawn $p_Spawn)
    {
        $this->m_Spawns[] = $p_Spawn;
    }

    public function blockSpawns()
    {
        foreach ($this->m_Spawns as $l_Spawn)
        {
            if ($l_Spawn instanceof Spawn)
                $l_Spawn->blockSpawn();
        }
    }

    public function unblockSpawns()
    {
        foreach ($this->m_Spawns as $l_Spawn)
        {
            if ($l_Spawn instanceof Spawn)
                $l_Spawn->unblockSpawn();
        }
    }

    public function getRandomEmptySpawn(): ?Spawn
    {
        foreach ($this->getSpawns() as $l_Spawn)
        {
            if ($l_Spawn instanceof Spawn)
            {
                if ($l_Spawn->isEmpty())
                    return $l_Spawn;
            }
        }

        return null;
    }

    //----------------
    // GETTERS
    //----------------
    /**
     * @return array
     */
    public function getSpawns(): array
    {
        return $this->m_Spawns;
    }

    public function getSpawnByName(string $p_Name): ?Spawn
    {
        foreach ($this->m_Spawns as $l_Spawn)
        {
            if ($l_Spawn instanceof Spawn)
            {
                if ($l_Spawn->getName() === $p_Name)
                    return $l_Spawn;
            }
        }
        return null;
    }
}