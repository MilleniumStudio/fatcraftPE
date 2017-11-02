<?php

namespace fatutils;

use fatutils\ban\BanManager;
use fatutils\commands\BanCommand;
use fatutils\commands\MuteCommand;
use fatutils\loot\ChestsManager;
use fatutils\permission\PermissionManager;
use fatutils\pets\PetsManager;
use fatutils\npcs\NpcsManager;
use fatutils\shop\ShopManager;
use fatutils\tools\RawParticle;
use fatutils\tools\WorldUtils;
use fatutils\tools\TextFormatter;
use fatutils\tools\SkinRepository;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class FatUtils extends PluginBase
{
    private $m_TemplateConfig = null;
    private static $m_Instance;
    public $rpcServer;

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
        $this->getCommand("gps")->setExecutor(new commands\GPSCommand());
        $this->getCommand("firestorm")->setExecutor(new commands\FirestormCommand());
        $this->getCommand("lang")->setExecutor(new commands\LanguageCommand());

        $this->getCommand("ban")->setExecutor(new BanCommand());
        $this->getCommand("mute")->setExecutor(new MuteCommand());
        $this->getCommand("pet")->setExecutor(PetsManager::getInstance());
        $this->getCommand("skin")->setExecutor(SkinRepository::getInstance());
        $this->getCommand("npcs")->setExecutor(NpcsManager::getInstance());


        WorldUtils::stopWorldsTime();
//        $this->rpcServer = new \fatutils\tools\control_socket\RPCServer($this);

		PermissionManager::getInstance();
		BanManager::getInstance(); // BanManager initialization
		TextFormatter::loadLanguages();

		ShopManager::getInstance();
    }

    public function onDisable()
    {
        if ($this->rpcServer != null)
        {
            $this->rpcServer->stop();
        }
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
                case "loc":
                case "location":
                    if ($sender instanceof Player)
                        $sender->sendMessage("CurrentLocation: " . WorldUtils::locationToString($sender->getLocation()));
                    break;
				case "mainshop":
					if ($sender instanceof Player)
						ShopManager::getInstance()->getShopMenu($sender)->open();
					break;
				case "mainshopreload":
					if ($sender instanceof Player)
						ShopManager::getInstance()->reload();
					break;
                case "atest":
                    if ($sender instanceof Player)
                    {
						(new RawParticle($sender->asVector3()->add(0, 2.5, 0), $args[1]))->playForPlayer($sender);
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
    public function getTemplateConfig(): ?Config
    {
        return $this->m_TemplateConfig;
    }

    /**
     * @return string
     */
    public function getPluginFile() : string{
        return $this->getFile();
    }
}
