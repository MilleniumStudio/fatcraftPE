<?php
namespace fatutils;

use fatutils\chests\ChestsManager;
use fatutils\tools\WorldUtils;
use hungergames\HungerGame;
use hungergames\LootTable;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class FatUtils extends PluginBase
{
    private $m_TemplateConfig;
	private static $m_Instance;

	public static function getInstance():FatUtils
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
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        var_dump($args);
        switch(strtolower($args[0]))
        {
            case "?":
            case "help":
                $sender->sendMessage("/fatUtils");
                $sender->sendMessage("  - help (or ?)");
                $sender->sendMessage("  - getPos");
                break;
            case "getpos":
                if($sender instanceof Player)
                    $sender->sendMessage("CurrentLocation: " . WorldUtils::locationToString($sender->getLocation()));
                break;
            case "atest":
                if($sender instanceof Player)
                {
                    $sender->sendTip("ceci est un tips");
                    $sender->sendPopup("Ceci est un popup Title", "Ceci est un popup subtitle");
                }
                break;
            case "fillchests":
                ChestsManager::getInstance()->fillChests(LootTable::$m_GeneralLoot);
            default;
        }

        return true;
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

    /**
     * @return array
     */
    public function getSpawns():array
    {
        return $this->m_Spawns;
    }
}
