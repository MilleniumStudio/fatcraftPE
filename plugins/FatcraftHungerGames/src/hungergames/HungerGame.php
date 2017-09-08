<?php
namespace hungergames;
use fatutils\chests\ChestsManager;
use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\tools\WorldUtils;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class HungerGame extends PluginBase
{
	private $m_HungerGameConfig;
	private static $m_Instance;

	public static function getInstance():HungerGame
	{
		return self::$m_Instance;
	}

	public function onLoad()
	{
		self::$m_Instance = $this;
	}

    public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		FatUtils::getInstance()->setTemplateConfig($this->getConfig());
		$this->m_HungerGameConfig = new HungerGameConfig($this->getConfig());
		$this->initialize();
    }

    private function initialize()
	{
		ChestsManager::getInstance()->fillChests(LootTable::$m_GeneralLoot);
		SpawnManager::getInstance()->blockSpawns();
	}

    public function handlePlayerConnection(Player $p_Player)
    {
        if (GameManager::getInstance()->isWaiting()) {
            foreach (HungerGame::getInstance()->getHungerGameConfig()->getSlots() as $l_Slot) {
                if ($l_Slot instanceof Location) {
                    $l_NearbyEntities = $l_Slot->getLevel()
                        ->getNearbyEntities(WorldUtils::getRadiusBB($l_Slot, doubleval(1)));

                    if (count($l_NearbyEntities) == 0) {
                        echo $l_Slot . " available !\n";
                        $p_Player->teleport($l_Slot);
                        break;
                    } else
                        echo $l_Slot . " not available\n";
                }
            }
        }
    }

    //---------------------
    // UTILS
    //---------------------
	public function startGame()
	{
		foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
		{
			PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
			if ($this->getHungerGameConfig()->isSkyWars())
				$l_Player->setGamemode(0);
		}

		SpawnManager::getInstance()->unblockSpawns();
	}

	//---------------------
    // GETTERS
	//---------------------
    /**
     * @return mixed
     */
    public function getHungerGameConfig():HungerGameConfig
    {
        return $this->m_HungerGameConfig;
    }
}
