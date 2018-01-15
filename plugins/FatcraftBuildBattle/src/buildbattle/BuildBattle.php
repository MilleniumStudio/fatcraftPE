<?php

namespace buildbattle;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\spawns\Spawn;
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
use pocketmine\utils\UUID;


class BuildBattle extends PluginBase implements Listener
{
	private static $m_Instance;

	private $m_buildBattleConfig;
	private $m_waitingTimer;
	private $m_playTimer;
	private $m_voteTimer;

	private $m_spawnMap = [];

	private $m_gameStarted;
	private $m_challengeList = [];
	private $m_currentChallenge;

	private $m_ladderBoard = [];
	private $m_currentPlayerUUID;
	private $m_currentRoundVoteBoard = [];


	const CHALLENGE_LIST_CONF_STR = "challengeList";
	const CHALLENGE_CONF_STR = "challenge";

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
		$this->loadChallengeList();
		$this->m_currentChallenge = $this->getRandomChallenge();

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

	public function handlePlayerConnection(Player $p_Player)
	{
		$l_Spawn = SpawnManager::getInstance()->getRandomEmptySpawn();
		if (GameManager::getInstance()->isWaiting() && isset($l_Spawn)) {
			$l_Spawn->teleport($p_Player);

			$this->getLogger()->info("onlinePlayers: " . count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer());
			echo("MAX player : " . PlayersManager::getInstance()->getMaxPlayer() . " \n");
			echo("MIN player : " . PlayersManager::getInstance()->getMinPlayer() . " \n");
			echo("CURRENT players : " . count($this->getServer()->getOnlinePlayers()) . " \n");

			if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMaxPlayer()) {
				$this->getLogger()->info("MAX PLAYER REACH !");
				if ($this->m_waitingTimer instanceof Timer)
					$this->m_waitingTimer->cancel();
				$this->startGame();
			} else if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer()) {
				$this->getLogger()->info("MIN PLAYER REACH !");
				if ($this->m_waitingTimer instanceof Timer)
					$this->m_waitingTimer->start();
			} else {
				$p_Player->setGamemode(Player::CREATIVE);
				$this->getServer()->getLogger()->info($p_Player->getName() . " has been set to CREATIVE");
			}
		}

		$this->m_spawnMap[$p_Player->getUniqueId()->toString()] = $l_Spawn;
		var_dump($this->m_spawnMap);
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
		Sidebar::getInstance()->addLine("Build Battle");

		$this->m_playTimer = new DisplayableTimer(GameManager::getInstance()->getPlayingTickDuration());
		$this->m_playTimer
			->setTitle(new TextFormatter("timer.playing.title"))
			->addStopCallback(function () {
				$this->voteTime();
			});

		Sidebar::getInstance()
			->addWhiteSpace()
			->addTimer($this->m_playTimer)
			->addWhiteSpace()
			->addMutableLine(function () {
				return new TextFormatter("buildbattle.theme", ["theme" => $this->m_currentChallenge]);
			});
		Sidebar::getInstance()->update();

		// PREPARING PLAYERS
		foreach ($this->getServer()->getOnlinePlayers() as $l_Player) {
			PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
		}

		$this->m_gameStarted = true;

		// START PLAY TIMER
		$this->m_playTimer->start();

		// UNBLOCKING SPAWNS
		SpawnManager::getInstance()->unblockSpawns();
	}

	private function handleVoteTimer()
	{
		$this->m_voteTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration());
		$this->m_voteTimer
			->setTitle(new TextFormatter("timer.waiting.title"))
			->addStopCallback(function () {
				$this->nextVoteRound();
			})
			->addStartCallback(function ()
			{
 			});

		Sidebar::getInstance()
			->addTimer($this->m_voteTimer)
			->addWhiteSpace()
			->addMutableLine(function () {
				return new TextFormatter("game.waitingForMore", ["amount" => max(0, PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers()))]);
			});

		$this->m_voteTimer->start();
	}

	private function nextVoteRound()
	{
		// count points if needed
		if (count($this->m_currentRoundVoteBoard) > 0) {
			foreach ($this->m_currentRoundVoteBoard as $l_value) {
				$this->m_ladderBoard[$this->m_currentPlayerUUID] += $l_value;
			}
		}

		unset($this->m_currentRoundVoteBoard);
		$this->m_currentRoundVoteBoard = [];
		var_dump($this->m_spawnMap);
		if (count($this->m_spawnMap) <= 0)
		{
			echo("finish game \n");
			return; // finish game
		}

		$l_array_top_value = reset($this->m_spawnMap);
		$l_array_top_key = key($this->m_spawnMap);

		if ($l_array_top_value instanceof Spawn)
		{
			echo("location to tp = x : " . $l_array_top_value->getLocation()->x . " z : " . $l_array_top_value->getLocation()->z . "\n");
			foreach (PlayersManager::getInstance()->getInGamePlayers() as $l_player) {
				if ($l_player instanceof Player && $l_array_top_value instanceof Spawn)
				{
					$l_array_top_value->teleport($l_player);
					//$l_player->teleport($l_array_top_value->getLocation());
					echo("player " . $l_player->getName() . " TP\n");
				}
				echo("check\n");

			}
		}

		$this->m_currentPlayerUUID = $l_array_top_key;

		// remove player from spawn list for next vote rounds
		unset($this->m_spawnMap[$l_array_top_key]);

		$this->handleVoteTimer();
	}

	private function voteTime()
	{
		$this->m_gameStarted = false;

		foreach (PlayersManager::getInstance()->getInGamePlayers() as $l_player)
		{
			if ($l_player instanceof Player) {
				$l_player->setGamemode(Player::SPECTATOR);
				// init ladder board for each remaining player in the game
				$this->m_ladderBoard[$l_player->getUniqueId()->toString()] = 0;
			}
		}
		var_dump($this->m_ladderBoard);
		echo ("should start vote thing\n");
		$this->nextVoteRound();
		// create menu
		// set the menu in the main bar
		// deal with vote timer 20 seconds
		// update laderBoard
		// finish the game and give rewards
	}

	public function voteAddCurrentArea(Player $p_voter, int $p_score)
	{
		$this->m_currentRoundVoteBoard[$p_voter->getUniqueId()->toString()] = $p_score;
	}

	private function loadChallengeList()
	{
		FatUtils::getInstance()->getLogger()->info("Build Battle loading challenges...");
		foreach (FatUtils::getInstance()->getTemplateConfig()->get(BuildBattle::CHALLENGE_LIST_CONF_STR) as $l_challengesKey => $l_challengesValue)
		{
			if (is_array($l_challengesValue) && array_key_exists(BuildBattle::CHALLENGE_CONF_STR, $l_challengesValue))
			{
				echo ("challenge : " . $l_challengesValue[BuildBattle::CHALLENGE_CONF_STR] . "\n");
				$this->m_challengeList[] = $l_challengesValue[BuildBattle::CHALLENGE_CONF_STR];
			}
		}

	}

	private function getRandomChallenge() : String
	{
		$value = rand(0, count($this->m_challengeList) - 1);

		return $this->m_challengeList[$value];
	}


	/**
	 * @param BlockBreakEvent $e
	 */
	public function onBlockBreak(BlockBreakEvent $e)
	{
		if (!$this->m_gameStarted || !$e->getBlock()->hasMetadata("isCustom"))
		{
			$e->setCancelled(true);
		}
	}

	public function onBlockPlace(BlockPlaceEvent $e)
	{
		if (!$this->m_gameStarted)
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