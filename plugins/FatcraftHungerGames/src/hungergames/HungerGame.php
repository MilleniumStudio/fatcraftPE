<?php

namespace hungergames;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\loot\ChestsManager;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\scores\PlayerScoresManager;
use fatutils\scores\ScoresManager;
use fatutils\tools\bossBarAPI\BossBarAPI;
use fatutils\tools\DelayedExec;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\Timer;
use fatutils\tools\WorldUtils;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use fatutils\tools\MathUtils;
use fatutils\tools\TipsTimer;
use pocketmine\entity\Effect;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class HungerGame extends PluginBase implements Listener
{
    private $m_HungerGameConfig;
    private static $m_Instance;
    private $m_WaitingTimer;
    private $m_PlayTimer;

    public static function getInstance(): HungerGame
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
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        $this->m_HungerGameConfig = new HungerGameConfig($this->getConfig());
        $this->initialize();
    }

    private function initialize()
    {
        SpawnManager::getInstance()->blockSpawns();
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);
        WorldUtils::stopWorldsTime();

		if ($this->getHungerGameConfig()->isSkyWars())
			Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.sw"));
		else
			Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.hg"));

		Sidebar::getInstance()
			->addWhiteSpace()
            ->addMutableLine(function ()
            {
                return new TextFormatter("hungergame.alivePlayer", ["nbr" => PlayersManager::getInstance()->getAlivePlayerLeft()]);
            });

        GameManager::getInstance(); // not sure why this line is here
    }

    public function handlePlayerConnection(Player $p_Player)
    {
        $l_Spawn = SpawnManager::getInstance()->getRandomEmptySpawn();
        if (GameManager::getInstance()->isWaiting() && isset($l_Spawn))
        {
            $l_Spawn->teleport($p_Player);

            $this->getLogger()->info("onlinePlayers: " . count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer());
            if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMaxPlayer())
            {
                $this->getLogger()->info("MAX PLAYER REACH !");
                if ($this->m_WaitingTimer instanceof Timer)
                    $this->m_WaitingTimer->cancel();
                $this->startGame();
            }
            else if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer())
            {
                if (is_null($this->m_WaitingTimer))
                {
                    $this->getLogger()->info("MIN PLAYER REACH !");
                    $this->m_WaitingTimer = (new TipsTimer(GameManager::getInstance()->getWaitingTickDuration()))
                        ->setTitle(new TextFormatter("timer.waiting.title"))
                        ->addStopCallback(function ()
                        {
                            $this->startGame();
                        })
                        ->start();
                }
            }
            else if (count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer())
            {
                $l_WaitingFor = PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers());
                foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
                    $l_Player->sendTip((new TextFormatter("game.waitingForMore", ["amount" => $l_WaitingFor]))->asStringForPlayer($l_Player));
            }

        } else
        {
            $p_Player->setGamemode(3);
            $p_Player->sendMessage(TextFormat::YELLOW . "You've been automatically set to SPECTATOR");
            $this->getServer()->getLogger()->info($p_Player->getName() . " has been set to SPECTATOR");
        }

        Sidebar::getInstance()->update();
    }

    //---------------------
    // UTILS
    //---------------------
    public function startGame()
    {
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);
        ChestsManager::getInstance()->fillChests();

        foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
        {
            PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
            if ($this->getHungerGameConfig()->isSkyWars())
            {
                $l_Player->getInventory()->addItem(ItemFactory::get(ItemIds::STONE_PICKAXE));
                $l_Player->setGamemode(Player::SURVIVAL);
            }
            else
            {
                $l_Player->setGamemode(Player::ADVENTURE);
                $l_Player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(10)->setDuration(30 * 20));
            }
            $l_Player->addTitle(TextFormat::GREEN . "GO !");
        }

        $this->m_PlayTimer = (new TipsTimer(GameManager::getInstance()->getPlayingTickDuration()))
            ->setTitle(new TextFormatter("timer.playing.title"))
            ->addStopCallback(function () {
                if (PlayersManager::getInstance()->getAlivePlayerLeft() <= 1)
                    $this->endGame();
                else
                {
                    $l_ArenaLoc = Location::fromObject($this->getHungerGameConfig()->getDeathArenaLoc());

                    foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                    {
                        $l_Player->addTitle("", (new TextFormatter("hungergame.deathMatch"))->asStringForPlayer($l_Player));
                        $l_Player->teleport(WorldUtils::getRandomizedLocation($l_ArenaLoc, 3, 0, 3));
                        $l_Player->sendTip((new TextFormatter("hungergame.invulnerable", ["timesec" => 5]))->asStringForPlayer($l_Player));
                        $l_Player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(10)->setDuration(5 * 20));
                    }
                }
            })
            ->start();

        GameManager::getInstance()->startGame();
        SpawnManager::getInstance()->unblockSpawns();
    }

    public function endGame()
    {
        if ($this->m_PlayTimer instanceof Timer)
            $this->m_PlayTimer->cancel();

        $winners = PlayersManager::getInstance()->getAlivePlayers();
        $winnerName = "";
        if (count($winners) > 0)
        {
            $winner = $winners[0];
            if ($winner instanceof FatPlayer)
            {
                $winnerName = $winner->getPlayer()->getName();
                PlayerScoresManager::getInstance()->registerPlayer($winner->getPlayer());
            }
        }
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
        {
            $l_Player->addTitle(
                (new TextFormatter("game.end"))->asStringForPlayer($l_Player),
                (new TextFormatter("game.winner.single"))->addParam("name", $winnerName)->asStringForPlayer($l_Player),
                30, 80, 30);
        }

        PlayerScoresManager::getInstance()->giveRewards();
        
        (new TipsTimer(150))
            ->setTitle(new TextFormatter("timer.returnToLobby"))
            ->addStopCallback(function ()
            {
                foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                    LoadBalancer::getInstance()->balancePlayer($l_Player, "lobby");

                new DelayedExec(100, function () {
                    $this->getServer()->shutdown();
                });
            })
            ->start();

        GameManager::getInstance()->endGame();
    }

    //---------------------
    // EVENTS
    //---------------------
    public function playerQuitEvent(PlayerQuitEvent $e)
    {
        new DelayedExec(1, function () {
            if (GameManager::getInstance()->isWaiting())
            {
                if ($this->m_WaitingTimer instanceof Timer && $this->m_WaitingTimer->getTickLeft() > 0 &&
                    (count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer()))
                {
                    $this->m_WaitingTimer->cancel();
                    $this->m_WaitingTimer = null;
                }
            } else if (GameManager::getInstance()->isPlaying())
            {
                if (count($this->getServer()->getOnlinePlayers()) == 0)
                    $this->getServer()->shutdown();
            }
        });
    }

    //---------------------
    // GETTERS
    //---------------------
    /**
     * @return mixed
     */
    public function getHungerGameConfig(): HungerGameConfig
    {
        return $this->m_HungerGameConfig;
    }
}
