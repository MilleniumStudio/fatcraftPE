<?php
namespace hungergames;
use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\tools\WorldUtils;
use plugins\FatUtils\src\fatutils\game\GameManager;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Chest;

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
		$this->fillChests();
		$this->blockSlots();
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

		$this->unblockSlots();
	}

    public function fillChests()
    {
        foreach ($this->getHungerGameConfig()->getChests() as $l_ChestLocation)
        {
            if ($l_ChestLocation instanceof Location)
            {
                $l_ChestBlock = $l_ChestLocation->getLevel()->getBlock($l_ChestLocation);
                if ($l_ChestBlock->getId() == Block::CHEST || $l_ChestBlock->getId() == Block::TRAPPED_CHEST)
                {
                    $l_ChestTile = $l_ChestLocation->getLevel()->getTile($l_ChestBlock);
                    echo "ChestAT: " . WorldUtils::locationToString($l_ChestLocation) . " " .$l_ChestBlock->getId() . " tile=" . $l_ChestTile . "\n";
                    if ($l_ChestTile instanceof Chest)
                    {
                        $l_ChestTile->getInventory()->clearAll();
                        for ($i = 0, $l = rand(2, 10); $i <= $l; $i++)
                        {
                            $slot = rand(0, $l_ChestTile->getInventory()->getSize() - 1);
                            $item = new Item(LootTable::getHGRandomLootId());
                            echo "item[". $slot ."]= ". $item . "\n";
                            $l_ChestTile->getInventory()->setItem($slot, $item);
                        }
                    }
                }
                echo "NoChestAT: " . WorldUtils::locationToString($l_ChestLocation) . "...\n";
            }
        }
    }

	private function blockSlots()
	{
		foreach($this->getHungerGameConfig()->getSlots() as $l_Slot)
		{
			if ($l_Slot instanceof Location)
			{
				$l_SlotBlock = $l_Slot->getLevel()->getBlock($l_Slot);
				WorldUtils::setBlocksId([
					WorldUtils::getRelativeBlock($l_SlotBlock, -1, 0, 0),
					WorldUtils::getRelativeBlock($l_SlotBlock, 1, 0, 0),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 0, -1),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 0, 1),
					WorldUtils::getRelativeBlock($l_SlotBlock, -1, 1, 0),
					WorldUtils::getRelativeBlock($l_SlotBlock, 1, 1, 0),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 1, -1),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 1, 1),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 2, 0)
				], Block::INVISIBLE_BEDROCK);
			}
		}
	}

	private function unblockSlots()
	{
		foreach($this->getHungerGameConfig()->getSlots() as $l_Slot)
		{
			if ($l_Slot instanceof Location)
			{
				$l_SlotBlock = $l_Slot->getLevel()->getBlock($l_Slot);
				WorldUtils::setBlocksId([
					WorldUtils::getRelativeBlock($l_SlotBlock, -1, 0, 0),
					WorldUtils::getRelativeBlock($l_SlotBlock, 1, 0, 0),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 0, -1),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 0, 1),
					WorldUtils::getRelativeBlock($l_SlotBlock, -1, 1, 0),
					WorldUtils::getRelativeBlock($l_SlotBlock, 1, 1, 0),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 1, -1),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 1, 1),
					WorldUtils::getRelativeBlock($l_SlotBlock, 0, 2, 0)
				], Block::AIR);
			}
		}
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
