<?php

namespace hungergames;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\loot\ChestsManager;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\tools\bossBarAPI\BossBarAPI;
use fatutils\tools\Sidebar;
use fatutils\tools\Timer;
use fatutils\tools\WorldUtils;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use fatutils\tools\MathUtils;
use fatutils\tools\BossbarTimer;
use pocketmine\entity\Effect;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class HungerGame extends PluginBase
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

        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        $this->m_HungerGameConfig = new HungerGameConfig($this->getConfig());
        $this->initialize();
    }

    private function initialize()
    {
        SpawnManager::getInstance()->blockSpawns();
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);
        PlayersManager::getInstance()->displayHealth();
        WorldUtils::stopWorldsTime();

        if ($this->getHungerGameConfig()->isSkyWars())
		{
			Sidebar::getInstance()
				->addLine(TextFormat::GOLD . TextFormat::BOLD . "SkyWars")
				->addWhiteSpace()
				->addMutableLine(function ()
				{
					return TextFormat::AQUA . "Joueur en vie: " . TextFormat::RESET . TextFormat::BOLD . PlayersManager::getInstance()->getAlivePlayerLeft();
				});
		}
		else
		{
			Sidebar::getInstance()
				->addLine(TextFormat::GOLD . TextFormat::BOLD . "HungerGame")
				->addWhiteSpace()
				->addMutableLine(function ()
				{
					return TextFormat::AQUA . "Joueur en vie: " . TextFormat::RESET . TextFormat::BOLD . PlayersManager::getInstance()->getAlivePlayerLeft();
				});
		}
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
                    $this->m_WaitingTimer = (new BossbarTimer(GameManager::getInstance()->getWaitingTickDuration()))
                        ->setTitle("Debut dans")
                        ->addStopCallback(function ()
                        {
                            $this->startGame();
                        })
                        ->start();
                }
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

        $this->m_PlayTimer = (new BossbarTimer(GameManager::getInstance()->getPlayingTickDuration()))
            ->setTitle("Fin de la partie dans")
            ->addStopCallback(function () {
                if (PlayersManager::getInstance()->getAlivePlayerLeft() <= 1)
                    $this->endGame();
                else
                {
                    $l_ArenaLoc = Location::fromObject($this->getHungerGameConfig()->getDeathArenaLoc());

                    foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                    {
                        $l_Player->addSubTitle(TextFormat::DARK_AQUA . TextFormat::BOLD . "Timer terminé, match à mort dans l'arène !");
                        $l_Player->teleport(WorldUtils::getRandomizedLocation($l_ArenaLoc, 3, 0, 3));
                        $l_Player->sendTip("Vous êtes invulnérable pendant 5 secondes");
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
                score\HungerGameScoreManager::getInstance()->registerDeath($winner->getPlayer());
            }
        }
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $l_Player->addTitle(TextFormat::DARK_AQUA . TextFormat::BOLD . "Partie terminée", TextFormat::GREEN . TextFormat::BOLD . "le vainqueur est " . $winnerName, 30, 80, 30);

        (new BossbarTimer(150))
            ->setTitle("Retour au lobby")
            ->addStopCallback(function ()
            {
                $this->getServer()->shutdown();
            })
            ->start();

        score\HungerGameScoreManager::getInstance()->giveRewards();

        GameManager::getInstance()->endGame();
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
