<?php

namespace fatutils;

use fatutils\loot\ChestsManager;
use fatutils\tools\TextFormatter;
use fatutils\tools\WorldUtils;
use hungergames\HungerGame;
use hungergames\LootTable;
use fatutils\tools\Sidebar;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\sound\GenericSound;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
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

//        echo (new TextFormatter("player.earn"))
//                ->addParam("name", "MACHIN")
//                ->addParam("quantity", 5)
//                ->addParam("moneyName", new TextFormatter("money.gold.name", [
//                    "version" => "v0.1"
//                ]))
//                ->asString() . "\n";
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
                    ChestsManager::getInstance()->fillChests();
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
