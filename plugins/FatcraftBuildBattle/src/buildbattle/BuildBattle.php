<?php

namespace buildbattle;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\tools\WorldUtils;
use fatutils\tools\schedulers\DisplayableTimer;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\metadata\MetadataValue;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\schedulers\Timer;


class BuildBattle extends PluginBase implements Listener
{
	private static $m_Instance;

	private $m_buildBattleConfig;
	private $m_WaitingTimer;
	private $m_gameStarted;

	public static function getInstance(): BuildBattle
	{
		return self::$m_Instance;
	}

	public function onLoad()
	{
		self::$m_Instance = $this;
	}

	public function onEnable()
	{
		$this->m_buildBattleConfig = new BuildBattleConfig($this->getConfig());
		FatUtils::getInstance()->setTemplateConfig($this->getConfig());
		if ($this->m_buildBattleConfig->getEndGameTime() == 0)
			$this->getLogger()->critical("FatcraftBuildBattle : ERROR : end game timer == 0 (failed at loading conf ?)");
		else
			$this->initialize();
	}

	public function onDisable()
	{
	}

	public function initialize()
	{
		SpawnManager::getInstance()->blockSpawns();
		LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);
		WorldUtils::stopWorldsTime();
		$this->m_gameStarted = false;

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

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
		$l_Spawn = SpawnManager::getInstance()->getRandomEmptySpawn();
		if (GameManager::getInstance()->isWaiting() && isset($l_Spawn))
		{
			$l_Spawn->teleport($p_Player);

			$this->getLogger()->info("onlinePlayers: " . count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer());
			echo ("MAX player : " . PlayersManager::getInstance()->getMaxPlayer() . " \n");
			echo ("MIN player : " . PlayersManager::getInstance()->getMinPlayer() . " \n");
			echo ("CURRENT players : " . count($this->getServer()->getOnlinePlayers()) . " \n");

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
				{
					echo ("timer should start");
					$this->m_WaitingTimer->start();
				}
			} else
			{
				$p_Player->setGamemode(Player::CREATIVE);
				$p_Player->sendMessage(TextFormat::YELLOW . "You've been automatically set to SPECTATOR");
				$this->getServer()->getLogger()->info($p_Player->getName() . " has been set to SPECTATOR");
			}
		}

		Sidebar::getInstance()->update();
	}

	/**
	 * @param PlayerJoinEvent $e
	 */
	public function onSpawn(PlayerJoinEvent $e)
	{
		$l_player = $e->getPlayer();
		$l_player->getInventory()->clearAll();

		$this->handlePlayerConnection($l_player);
	}

	public function startGame()
	{
		// CLOSING SERVER
		LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);
		GameManager::getInstance()->startGame();

		// INIT SIDEBAR
		Sidebar::getInstance()->clearLines();
		Sidebar::getInstance()->addTranslatedLine(new TextFormatter("CHANGE TEMPLATE"));

		$this->m_PlayTimer = new DisplayableTimer(GameManager::getInstance()->getPlayingTickDuration());
		$this->m_PlayTimer
			->setTitle(new TextFormatter("timer.playing.title"))
			->addStopCallback(function ()
			{
				if (PlayersManager::getInstance()->getInGamePlayerLeft() <= 1)
					$this->endGame();
				else
				{
					foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
					{
						$l_Player->addTitle("", (new TextFormatter("CHANGE TEMPLATE AS WELL"))->asStringForPlayer($l_Player));
						$l_Player->sendTip((new TextFormatter("CHANGE TEMPLATE AS WELL HERE", ["timesec" => 5]))->asStringForPlayer($l_Player));
					}
				}
			});

		Sidebar::getInstance()
			->addWhiteSpace()
			->addTimer($this->m_PlayTimer)
			->addWhiteSpace()
			->addMutableLine(function ()
			{
				return new TextFormatter("CHANGE TEMPLATE AS WELL HERE TOO", ["nbr" => PlayersManager::getInstance()->getInGamePlayerLeft()]);
			});

		// PREPARING PLAYERS
		foreach ($this->getServer()->getOnlinePlayers() as $l_Player) {
			PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
			$l_Player->addTitle(TextFormat::GREEN . "GO !");
		}

		$this->m_gameStarted= true;
	}

	/**
	 * @param BlockBreakEvent $e
	 */
	public function onBlockBreak(BlockBreakEvent $e)
	{
		if (!$e->getBlock()->hasMetadata("isCustom"))
		{
			$e->setCancelled(true);
		}
	}

	public function onBlockPlace(BlockPlaceEvent $e)
	{
		if ($this->m_gameStarted != true)
		{
			$e->setCancelled(true);
			return;
		}

		$e->getBlock()->setMetadata("isCustom", new class(BuildBattle::getInstance()) extends MetadataValue
		{
			/**
			 *  constructor.
			 */
			public function __construct(PluginBase $p_Plugin)
			{
				parent::__construct($p_Plugin);
			}


			/**
			 * Fetches the value of this metadata item.
			 *
			 * @return mixed
			 */
			public function value()
			{
				return true;
			}

			/**
			 * Invalidates this metadata item, forcing it to recompute when next
			 * accessed.
			 */
			public function invalidate()
			{
			}
		});
	}
}

?>