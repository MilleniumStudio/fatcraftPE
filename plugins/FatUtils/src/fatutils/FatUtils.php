<?php

namespace fatutils;

use fatcraft\bedwars\ShopKeeper;
use fatutils\ban\BanManager;
use fatutils\commands\BanCommand;
use fatutils\commands\MuteCommand;
use fatutils\loot\ChestsManager;
use fatutils\permission\PermissionManager;
use fatutils\players\PlayersManager;
use fatutils\tools\animations\CircleAnimation;
use fatutils\tools\WorldUtils;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\FormWindow;
use fatutils\ui\windows\ModalWindow;
use fatutils\tools\TextFormatter;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Location;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
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

        WorldUtils::stopWorldsTime();
//        $this->rpcServer = new \fatutils\tools\control_socket\RPCServer($this);

		PermissionManager::getInstance();
		BanManager::getInstance(); // BanManager initialization
		TextFormatter::loadLanguages();
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
                case "atest":
                    if ($sender instanceof Player)
                    {
						$l_Level = $sender->getLevel();
						(new CircleAnimation())
							->setEntity($sender)
							->setNbPoint(100)
							->setNbSubDivision(1)
							->setRadius(4)
							->setTickDuration(20 * 30)
							->setCallback(function($data) use ($l_Level)
							{
								if (gettype($data) === "array")
								{
									foreach ($data as $l_Location)
									{
										if ($l_Location instanceof Vector3)
										{
											$l_Level->addParticle(new RedstoneParticle($l_Location));
										}
									}
								}
							})
						->play();
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
}
