<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 5/11/18
 * Time: 5:55 PM
 */

namespace instagib;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\game\GameManager;
use fatutils\players\PlayersManager;
use fatutils\spawns\Spawn;
use fatutils\spawns\SpawnManager;
use fatutils\tools\schedulers\DisplayableTimer;
use fatutils\tools\schedulers\Timer;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\WorldUtils;
use InstagibConfig;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Instagib extends PluginBase
{
    private static $m_Instance;

    private $m_instagibConfig;
    private $m_waitingTimer;
    private $m_playTimer;
    private $m_gameStarted;

    private $m_scoreArray;

    public static function getInstance(): Instagib
    {
        return self::$m_Instance;
    }

    public function onLoad()
    {
        self::$m_Instance = $this;
    }

    public function onEnable()
    {
        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        $this->m_instagibConfig = new InstagibConfig($this->getConfig());
        if ($this->getConfig()->exists("spawns")) {
            foreach ($this->getConfig()->getNested("spawns") as $spawn) {
                var_dump($spawn);
                SpawnManager::getInstance()->addSpawn(new Spawn(WorldUtils::stringToLocation($spawn)));
            }
        } else {
            echo "pas de spawns ?\n";
        }

        var_dump($this->m_instagibConfig);
        var_dump(SpawnManager::getInstance()->getSpawns());
        if ($this->m_instagibConfig->getEndGameTime() == 0)
            $this->getLogger()->critical("FatcraftInstagib : ERROR : end game timer == 0 (failed at loading conf ?)");
        else
            $this->initialize();
    }

    public function onDisable()
    {
    }

    public function initialize()
    {
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);
        WorldUtils::stopWorldsTime();
        $this->m_gameStarted = false;

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->m_waitingTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration());
        $this->m_waitingTimer
            ->setTitle(new TextFormatter("timer.waiting.title"))
            ->addStopCallback(function () {
                $this->startGame();
            })
            ->addSecondCallback(function () {
                if ($this->m_waitingTimer instanceof Timer) {
                    $l_SecLeft = $this->m_waitingTimer->getSecondLeft();
                    $l_Text = "";
                    if ($l_SecLeft == 3)
                        $l_Text = TextFormat::RED . $l_SecLeft;
                    else if ($l_SecLeft == 2)
                        $l_Text = TextFormat::GOLD . $l_SecLeft;
                    else if ($l_SecLeft == 1)
                        $l_Text = TextFormat::YELLOW . $l_SecLeft;

                    foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                        $l_Player->addTitle($l_Text, "");
                }
            });

        Sidebar::getInstance()
            ->addLine("§4§lInstagib")
            ->addTranslatedLine(new TextFormatter("template.playfatcraft.purple"))
            ->addWhiteSpace()
            ->addTimer($this->m_waitingTimer)
            ->addWhiteSpace()
            ->addMutableLine(function () {
                return new TextFormatter("game.waitingForMore", ["amount" => max(0, PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers()))]);
            });
    }

    public function handlePlayerLogin(Player $p_player)
    {
        $p_player->setGamemode(Player::ADVENTURE);
        $p_player->getInventory()->clearAll();

        $p_player->addEffect(new EffectInstance(
            Effect::getEffect(Effect::JUMP_BOOST),
            INT32_MAX,
            2,
            0,
            0
        ));
        $p_player->addEffect(new EffectInstance(
            Effect::getEffect(Effect::SPEED),
            INT32_MAX,
            2,
            0,
            0
        ));

        if (GameManager::getInstance()->isWaiting()) {
            if (count(Instagib::getInstance()->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer()) {
                $this->getLogger()->info("MIN PLAYER REACH !");
                if ($this->m_waitingTimer instanceof Timer)
                    $this->m_waitingTimer->start();
            }
        }
    }

    public function startGame()
    {
        // CLOSING SERVER
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);
        GameManager::getInstance()->startGame();

        // INIT SIDEBAR
        Sidebar::getInstance()->clearLines();
        Sidebar::getInstance()->addLine("§4Instagib");

        $this->m_playTimer = new DisplayableTimer(GameManager::getInstance()->getPlayingTickDuration());
        $this->m_playTimer
            ->setTitle(new TextFormatter("timer.playing.title"))
            ->addStopCallback(function () {
            });

        // PREPARING PLAYERS
        foreach ($this->getServer()->getOnlinePlayers() as $l_Player) {
            PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
        }

        $this->m_gameStarted = true;

        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            $this->m_scoreArray[$player->getName()] = 0;
        }
        $this->handleSideBar();

        // START PLAY TIMER
        $this->m_playTimer->start();
        $this->m_gameStarted = true;
    }

    public function handleSideBar()
    {
        Sidebar::getInstance()->clearLines();

        Sidebar::getInstance()
            ->addLine("§4§lInstagib")
            ->addTranslatedLine(new TextFormatter("template.playfatcraft.purple"))
            ->addWhiteSpace()
            ->addTimer($this->m_playTimer)
            ->addWhiteSpace();

        foreach ($this->m_scoreArray as $name => $score) {
            Sidebar::getInstance()->addLine($name . " => " . $score);
        }

        Sidebar::getInstance()
            ->addWhiteSpace()
            ->addWhiteSpace()
            ->addWhiteSpace();

        Sidebar::getInstance()->update();
    }

    public function getInstagibConfig(): InstagibConfig
    {
        return ($this->m_instagibConfig);
    }

    public function scoreForPlayer(Player $p_player, int $p_value)
    {
        if (isset($this->m_scoreArray[$p_player->getName()])) {
            $this->m_scoreArray[$p_player->getName()] += $p_value;
        }
        arsort($this->m_scoreArray);
        $this->handleSideBar();
    }

    public function isGameStarted(): bool
    {
        return $this->m_gameStarted;
    }
}