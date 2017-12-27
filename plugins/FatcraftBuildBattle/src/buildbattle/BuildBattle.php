<?php

namespace buildbattle;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\tools\schedulers\Timer;
use fatutils\tools\WorldUtils;
use fatutils\tools\schedulers\DisplayableTimer;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class BuildBattle extends PluginBase implements Listener
{
	private static $m_Instance;

	private $m_buildBattleConfig;
	private $m_WaitingTimer;

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
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->m_WaitingTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration());
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
					$this->m_WaitingTimer->start();
			} else
			{
				$p_Player->setGamemode(3);
				$p_Player->sendMessage(TextFormat::YELLOW . "You've been automatically set to SPECTATOR");
				$this->getServer()->getLogger()->info($p_Player->getName() . " has been set to SPECTATOR");
			}
		}
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
/*		LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);
		GameManager::getInstance()->startGame();*/

	}
}

?>