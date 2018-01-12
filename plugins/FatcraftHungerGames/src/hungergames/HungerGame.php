<?php

namespace hungergames;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\loot\ChestsManager;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\scores\ScoresManager;
use fatutils\tools\schedulers\BossbarTimer;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\tools\schedulers\DisplayableTimer;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\schedulers\Timer;
use fatutils\tools\WorldUtils;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use fatutils\tools\schedulers\TipsTimer;
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

		GameManager::getInstance(); // not sure why this line is here

		$this->m_WaitingTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration());
		$this->m_WaitingTimer
			->setTitle(new TextFormatter("timer.waiting.title"))
			->addStopCallback(function ()
			{
				$this->startGame();
			})
			->addSecondCallback(function () {
				if ($this->m_WaitingTimer instanceof Timer)
				{
					$l_SecLeft = $this->m_WaitingTimer->getSecondLeft();
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

		if ($this->getHungerGameConfig()->isSkyWars())
			Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.sw"));
		else
			Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.hg"));

		Sidebar::getInstance()
			->addTimer($this->m_WaitingTimer)
			->addWhiteSpace()
			->addMutableLine(function ()
			{
				return new TextFormatter("game.waitingForMore", ["amount" => max(0, PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers()))]);
			});
    }

	public function handlePlayerConnection(Player $p_Player)
	{
		if ($this->getHungerGameConfig()->isSkyWars())
		{
			$p_Player->sendMessage((new TextFormatter("template.info.template", [
				"gameName" => new TextFormatter("template.sw"),
				"text" => new TextFormatter("template.info.sw")
			]))->asStringForPlayer($p_Player));
		} else
		{
			$p_Player->sendMessage((new TextFormatter("template.info.template", [
				"gameName" => new TextFormatter("template.hg"),
				"text" => new TextFormatter("template.info.hg")
			]))->asStringForPlayer($p_Player));
		}

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
			} else if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer())
			{
				$this->getLogger()->info("MIN PLAYER REACH !");
				if ($this->m_WaitingTimer instanceof Timer)
					$this->m_WaitingTimer->start();
			}
//            else if (count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer())
//            {
//                $l_WaitingFor = PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers());
//                foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
//					$l_Player->addTitle("", (new TextFormatter("game.waitingForMore", ["amount" => $l_WaitingFor]))->asStringForPlayer($l_Player), 1, 60, 1);
//            }

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
		// CLOSING SERVER
		LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);
		GameManager::getInstance()->startGame();

		// INIT SIDEBAR
		Sidebar::getInstance()->clearLines();
		if ($this->getHungerGameConfig()->isSkyWars())
			Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.sw"));
		else
			Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.hg"));

		$this->m_PlayTimer = new DisplayableTimer(GameManager::getInstance()->getPlayingTickDuration());
		$this->m_PlayTimer
			->setTitle(new TextFormatter("timer.playing.title"))
			->addStopCallback(function ()
			{
				if (PlayersManager::getInstance()->getInGamePlayerLeft() <= 1)
					$this->endGame();
				else
				{
					$l_ArenaLoc = Location::fromObject($this->getHungerGameConfig()->getDeathArenaLoc());
					$l_ArenaLocType = $this->getHungerGameConfig()->getMDeathArenaLocType();
					$l_ArenaLocRadius = $this->getHungerGameConfig()->getMDeathArenaLocRadius();
					foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
					{
 						$l_Player->addTitle("", (new TextFormatter("hungergame.deathMatch"))->asStringForPlayer($l_Player));
 						if ($l_ArenaLocType == HungerGameConfig::DEATH_ARENA_TYPE_PERIMETER)
 						    $l_Player->teleport(WorldUtils::getRandomizedLocationOnAreaEdge($l_ArenaLoc, floatval($l_ArenaLocRadius), 0, floatval($l_ArenaLocRadius)));
 						else
 						    $l_Player->teleport(WorldUtils::getRandomizedLocationWithinArea($l_ArenaLoc, floatval($l_ArenaLocRadius), 0, floatval($l_ArenaLocRadius)));
 						$l_Player->sendTip((new TextFormatter("hungergame.invulnerable", ["timesec" => 5]))->asStringForPlayer($l_Player));
 						$l_Player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(10)->setDuration(5 * 20));
					}
				}
			});

		Sidebar::getInstance()
			->addWhiteSpace()
			->addTimer($this->m_PlayTimer)
			->addWhiteSpace()
			->addMutableLine(function ()
			{
				return new TextFormatter("hungergame.alivePlayer", ["nbr" => PlayersManager::getInstance()->getInGamePlayerLeft()]);
			});

		// FILLING UP CHEST
		ChestsManager::getInstance()->fillChests();

		// PREPARING PLAYERS
		foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
		{
			PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
			if ($this->getHungerGameConfig()->isSkyWars())
			{
				$l_Player->getInventory()->addItem(ItemFactory::get(ItemIds::STONE_PICKAXE));
				$l_Player->setGamemode(Player::SURVIVAL);
			} else
			{
				$l_Player->setGamemode(Player::ADVENTURE);
				$l_Player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(10)->setDuration(30 * 20));
			}

			PlayersManager::getInstance()->getFatPlayer($l_Player)->equipKitToPlayer();

			$l_Player->addTitle(TextFormat::GREEN . "GO !");
		}

		// START PLAY TIMER
		$this->m_PlayTimer->start();

		// UNBLOCKING SPAWNS
		SpawnManager::getInstance()->unblockSpawns();

		Sidebar::getInstance()->update();
	}

	public function endGame()
	{
		if ($this->m_PlayTimer instanceof Timer)
			$this->m_PlayTimer->cancel();

		GameManager::getInstance()->endGame();

		$winners = PlayersManager::getInstance()->getInGamePlayers();
		$winnerName = "";
		if (count($winners) > 0)
		{
			$winner = $winners[0];
			if ($winner instanceof FatPlayer)
			{
				$winnerName = $winner->getPlayer()->getName();
				ScoresManager::getInstance()->giveRewardToPlayer($winner->getPlayer()->getUniqueId(), 1);
			}
		}

		foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
		{
			$l_Player->addTitle(
				(new TextFormatter("game.end"))->asStringForPlayer($l_Player),
				(new TextFormatter("game.winner.single"))->addParam("name", $winnerName)->asStringForPlayer($l_Player),
				30, 100, 30);
		}

		(new BossbarTimer(150))
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

	//---------------------
	// EVENTS
	//---------------------
	public function playerQuitEvent(PlayerQuitEvent $e)
	{
		if (GameManager::getInstance()->isPlaying())
		{
			$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($e->getPlayer());
			if ($l_FatPlayer != null)
				$l_FatPlayer->setOutOfGame();
		}

		Sidebar::getInstance()->update();

		new DelayedExec(function ()
		{
			if (GameManager::getInstance()->isWaiting())
			{
				if ($this->m_WaitingTimer instanceof Timer && $this->m_WaitingTimer->getTickLeft() > 0 &&
					(count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer()))
				{
					$this->m_WaitingTimer->cancel();
					$this->m_WaitingTimer = null;
					$this->resetGameWaiting();
				}
			} else if (GameManager::getInstance()->isPlaying())
			{
				if (count($this->getServer()->getOnlinePlayers()) == 0)
					$this->getServer()->shutdown();
			}
		}, 1);
	}

	private function resetGameWaiting()
	{
		// Waiting Clock Initialization
		$this->m_WaitingTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration());
		$this->m_WaitingTimer
			->setTitle(new TextFormatter("timer.waiting.title"))
			->addStopCallback(function ()
			{
				$this->startGame();
			})
			->addSecondCallback(function () {
				if ($this->m_WaitingTimer instanceof Timer)
				{
					$l_SecLeft = $this->m_WaitingTimer->getSecondLeft();
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

		Sidebar::getInstance()->clearLines();
		// Waiting Sidebar Initialization
		Sidebar::getInstance()
			->addTranslatedLine(new TextFormatter("template.br"))
			->addTimer($this->m_WaitingTimer)
			->addWhiteSpace()
			->addMutableLine(function ()
			{
				return new TextFormatter("game.waitingForMore", ["amount" => max(0, PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers()))]);
			});
		Sidebar::getInstance()->update();
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
