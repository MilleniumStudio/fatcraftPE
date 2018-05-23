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
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\scores\ScoresManager;
use fatutils\spawns\Spawn;
use fatutils\spawns\SpawnManager;
use fatutils\tools\schedulers\BossbarTimer;
use fatutils\tools\schedulers\DelayedExec;
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
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class Instagib extends PluginBase
{
    private static $m_Instance;

    const END_GAME_TIMER = "endGameTime";
    const KEY_SPAWNS = "spawns";


    private $m_instagibConfig;
    private $m_waitingTimer;
    private $m_playTimer;
    private $m_gameStarted;
    //private $m_endGameTimer;
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
                SpawnManager::getInstance()->addSpawn(new Spawn(WorldUtils::stringToLocation($spawn)));
            }
        } else {
            echo "pas de spawns ?\n";
        }

       // if ($this->getConfig()->exists(Instagib::END_GAME_TIMER))
        //    $this->m_endGameTimer = $this->getConfig()->get(Instagib::END_GAME_TIMER, 0);
        //else
        //    echo("endGameTime property does not exist in the config.yml\n");


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

        $this->m_waitingTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration() * 2);
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

        $l_Sniper = Item::get(ItemIds::ENDER_PEARL);
        $l_Sniper->setCustomName("§5Sniper");
        $p_player->getInventory()->setItem(0, $l_Sniper);

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

        $this->m_playTimer = new DisplayableTimer(4800);
        $this->m_playTimer
            ->setTitle(new TextFormatter("timer.playing.title"))
            ->addStopCallback(function () {
               $this->endGame();
            });

        // PREPARING PLAYERS
        foreach ($this->getServer()->getOnlinePlayers() as $l_Player) {
            PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();

            $nextSpawnLoc = SpawnManager::getInstance()->getRandomEmptySpawn();
            $l_Player->teleport($nextSpawnLoc->getLocation());
            $this->m_scoreArray[$l_Player->getName()] = 0;
        }

        $this->m_gameStarted = true;

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

    public function endGame()
    {
        if ($this->m_playTimer instanceof Timer)
            $this->m_playTimer->cancel();

        GameManager::getInstance()->endGame(false);

        foreach ($this->getServer()->getOnlinePlayers() as $player)
        {
            if ($player instanceof Player)
                ScoresManager::getInstance()->giveRewardToPlayer($player->getUniqueId(), 0.33);
        }

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new DisplayWinner($this), 10);
        (new BossbarTimer(200))
            ->setTitle(new TextFormatter("timer.returnToLobby"))
            ->addStopCallback(function ()
            {
                foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                    LoadBalancer::getInstance()->balancePlayer($l_Player, LoadBalancer::TEMPLATE_TYPE_LOBBY);

                new DelayedExec(function ()
                {
                    $this->getServer()->shutdown();
                }, 100);
            })
            ->start();
    }

    public function displayWinner()
    {
        $name = "";
        $score = 0;
        arsort($this->m_scoreArray);
        var_dump($this->m_scoreArray);
        foreach ($this->m_scoreArray as $l_name => $l_score)
        {
            $name = $l_name;
            $score = $l_score;
            break;
        }
        echo("name = " . $name . "\n");
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $l_Player->addTitle("§6".$name . " won !", "score : " . $score, -1, 3000);
    }
}

class DisplayWinner extends PluginTask
{
    public function __construct(Plugin $owner)
    {
        parent::__construct($owner);
    }

    public function onRun(int $currentTick)
    {
        Instagib::getInstance()->displayWinner();
    }

    public function cancel() {
        $this->getHandler()->cancel();
    }
}
