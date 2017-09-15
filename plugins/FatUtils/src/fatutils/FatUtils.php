<?php

namespace fatutils;

use fatutils\loot\ChestsManager;
use fatutils\tools\WorldUtils;
use hungergames\HungerGame;
use hungergames\LootTable;
use fatutils\tools\Sidebar;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\sound\GenericSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Chest;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class FatUtils extends PluginBase
{
    private $m_TemplateConfig = null;
    private static $m_Instance;

    public static function getInstance(): FatUtils
    {
        return self::$m_Instance;
    }

    public function onLoad()
    {
        self::$m_Instance = $this;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        WorldUtils::stopWorldsTime();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender->isOp())
        {
            switch (strtolower($args[0]))
            {
                case "?":
                case "help":
                    $sender->sendMessage("/fatUtils");
                    $sender->sendMessage("  - help (or ?)");
                    $sender->sendMessage("  - getPos");
                    break;
                case "getpos":
                    if ($sender instanceof Player)
                        $sender->sendMessage("CurrentLocation: " . WorldUtils::locationToString($sender->getLocation()));
                    break;
                case "atest":
                    if ($sender instanceof Player)
                    {
                        echo "====================\n";
                        $sender->sendTip("TIP");
                        $sender->addTitle("TITLE");
                        $sender->addSubTitle("SUBTITLE");
                    }
                    break;
                case "fillchests":
//                    ChestsManager::getInstance()->fillChests();
                    if ($sender instanceof Player) {
                        WorldUtils::loadChunkAt($sender->getPosition());
//                        $sender->getPosition()->getLevel()->setBlock($sender->getPosition(), BlockFactory::get(BlockIds::CHEST));
                        $l_ChestBlock = $sender->getPosition()->getLevel()->getBlock($sender->getPosition());
                        $l_ChestTile = $sender->getPosition()->getLevel()->getTile($l_ChestBlock);
                        var_dump($l_ChestTile);
                        if ($l_ChestTile instanceof Chest) {
                            $l_ChestTile->getInventory()->clearAll();

                            $l_InventoryTotalValue = 0;
                            for ($i = 0; $i < 10; $i++)
                            {
                                echo "fillingItem:\n";
                                $l_Item = ItemFactory::get(ItemIds::ACACIA_DOOR);
                                $nbt = $l_Item->getNamedTag() ?? new CompoundTag("", []);
                                $nbt->test = new StringTag("shop", "...");
//                                $nbt->offsetSet("shop", "salut");
                                $l_Item->setNamedTag($nbt);
                                $l_ChestTile->getInventory()->setItem($i, $l_Item);
                            }
                        }
                    }
                    break;
                default;
            }

            return true;
        }

        return false;
    }

    public function setTemplateConfig(Config $p_Config)
    {
        $this->m_TemplateConfig = $p_Config;
    }

    /**
     * @return mixed
     */
    public function getTemplateConfig(): Config
    {
        return $this->m_TemplateConfig;
    }
}
