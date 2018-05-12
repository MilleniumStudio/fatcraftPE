<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 5/11/18
 * Time: 5:55 PM
 */

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\game\GameManager;
use fatutils\players\PlayersManager;
use fatutils\tools\schedulers\DisplayableTimer;
use fatutils\tools\schedulers\Timer;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\WorldUtils;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Instagib extends PluginBase implements Listener
{
    private static $m_Instance;

    private $m_instagibConfig;
    private $m_waitingTimer;
    private $m_playTimer;
    private $m_gameStarted;

    public function onLoad()
    {
        self::$m_Instance = $this;
    }

    public function onEnable()
    {
        $this->m_instagibConfig = new InstagibConfig($this->getConfig());
        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
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
            ->addTimer($this->m_waitingTimer)
            ->addWhiteSpace()
            ->addMutableLine(function () {
                return new TextFormatter("game.waitingForMore", ["amount" => max(0, PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers()))]);
            });

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
            ->addStopCallback(function () {});

        Sidebar::getInstance()
            ->addWhiteSpace()
            ->addTimer($this->m_playTimer)
            ->addWhiteSpace()
            ->addMutableLine(function () {
                return new TextFormatter("ADD SOMETHING");
            });
        Sidebar::getInstance()->update();

        // PREPARING PLAYERS
        foreach ($this->getServer()->getOnlinePlayers() as $l_Player) {
            PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
        }

        $this->m_gameStarted = true;

        // START PLAY TIMER
        $this->m_playTimer->start();
    }


    /**
     * @param PlayerJoinEvent $e
     */
    public function onPlayerJoin(PlayerJoinEvent $e)
    {
        $l_Player = $e->getPlayer();
        echo ("YO !! player join event\n");
        if (GameManager::getInstance()->isPlaying() || count(LoadBalancer::getInstance()->getServer()->getOnlinePlayers()) > PlayersManager::getInstance()->getMaxPlayer())
        {
            if ($l_Player->isOp()) {
                $l_Player->setGamemode(3);
                PlayersManager::getInstance()->getFatPlayer($l_Player)->setOutOfGame();
                return;
            }
            else
            {
                LoadBalancer::getInstance()->balancePlayer($l_Player, LoadBalancer::TEMPLATE_TYPE_LOBBY);
                return;
            }
        }

        $l_Player = $e->getPlayer();
        $l_Player->setGamemode(Player::ADVENTURE);
        $l_Player->getInventory()->clearAll();

        $l_Player->addEffect(new EffectInstance(
            Effect::getEffect(Effect::JUMP_BOOST),
            INT32_MAX,
            2,
            0,
            0
        ));
        $l_Player->addEffect(new EffectInstance(
            Effect::getEffect(Effect::SPEED),
            INT32_MAX,
            2,
            0,
            0
        ));



        if (GameManager::getInstance()->isWaiting())
        {
            if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer())
            {
                $this->getLogger()->info("MIN PLAYER REACH !");
                if ($this->m_waitingTimer instanceof Timer)
                    $this->m_waitingTimer->start();
            }
        }
    }

    public function onBlocBreakEvent(BlockBreakEvent $e)
    {
        $e->setCancelled(true);
    }
}