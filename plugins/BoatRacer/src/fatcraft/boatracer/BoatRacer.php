<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 13:51
 */

namespace fatcraft\boatracer;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\game\GameManager;
use fatutils\players\PlayersManager;
use fatutils\spawns\SpawnManager;
use fatutils\tools\BossbarTimer;
use fatutils\tools\checkpoints\Checkpoint;
use fatutils\tools\checkpoints\CheckpointsPath;
use fatutils\tools\checkpoints\VolumeCheckpoint;
use fatutils\tools\DelayedExec;
use fatutils\tools\DisplayableTimer;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\Timer;
use fatutils\tools\volume\CuboidVolume;
use fatutils\tools\WorldUtils;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\entity\Entity;
use pocketmine\entity\Vehicle;
use pocketmine\entity\Boat as BoatEntity;
use pocketmine\event\entity\EntityVehicleExitEvent;
use pocketmine\utils\TextFormat;

class BoatRacer extends PluginBase implements Listener
{
	private static $m_Instance;
	private $m_WaitingTimer;

	/** @var CheckpointsPath */
	private $m_CheckpointPath = null;

	public static function getInstance(): BoatRacer
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
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->initialize();
	}

	private function initialize()
	{
		WorldUtils::setWorldsTime(0);
		WorldUtils::stopWorldsTime();

		$l_Checkpoints = $this->getConfig()->get("checkpoints");
		foreach ($l_Checkpoints as $l_RawCheckpointLoc)
		{
			if (is_null($this->m_CheckpointPath))
				$this->m_CheckpointPath = new CheckpointsPath();

			$this->m_CheckpointPath->addCheckpoint(new VolumeCheckpoint(CuboidVolume::createVolumeFromConfig($l_RawCheckpointLoc)));
		}

		$this->m_CheckpointPath
			->addStartCallback(function (Player $p_Player)
			{
				$this->applyBoat($p_Player);
			})
			->addCheckpointCallback(function (Player $p_Player, Checkpoint $p_Checkpoint)
			{
				Sidebar::getInstance()->updatePlayer($p_Player);
			})
			->addEndCallback(function (Player $p_Player)
			{
				$this->playerFinish($p_Player);
			})
			->disable();

		$this->m_WaitingTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration());
		$this->m_WaitingTimer
			->setTitle(new TextFormatter("timer.waiting.title"))
			->addStopCallback(function ()
			{
				$this->startGame();
			});

		Sidebar::getInstance()
			->addTranslatedLine(new TextFormatter("template.br"))
			->addTimer($this->m_WaitingTimer)
			->addWhiteSpace()
			->addMutableLine(function ()
			{
				return new TextFormatter("game.waitingForMore", ["amount" => PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers())]);
			});
	}

	//------------------------
	// GAME
	//------------------------
	public function startGame()
	{
		Sidebar::getInstance()
			->clearLines()
			->addTranslatedLine(new TextFormatter("template.br"))
			->addWhiteSpace()
			->addMutableLine(function (Player $p_Player)
			{
				$l_PlayerData = $this->m_CheckpointPath->getPlayerData($p_Player);
				if ($l_PlayerData != null)
					return new TextFormatter("boatracer.currentPos", ["pos" => ($l_PlayerData->getLastCheckpoint() != null ? $l_PlayerData->getLastCheckpoint()->getIndex() + 1 : 0), "total" => $this->m_CheckpointPath->getCheckpointCount()]);
				return [];
			});

		$l_Spawn = SpawnManager::getInstance()->getSpawnByName("playing");
		foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
		{
			$l_Spawn->teleport($l_Player, 1);
		}

		Sidebar::getInstance()->update();
		GameManager::getInstance()->startGame();

		new DelayedExec(function ()
		{
			$this->m_CheckpointPath->enable();
		}, 5);

	}

	public function playerFinish(Player $p_Player)
	{
		PlayersManager::getInstance()->getFatPlayer($p_Player)->setHasLost();
		$l_PlayerPos = PlayersManager::getInstance()->getAlivePlayerLeft() - GameManager::getInstance()->getPlayerNbrAtStart();

		echo $p_Player->getName() . " finished at " . $l_PlayerPos . "\n";

		if ($l_PlayerPos == 1)
			$l_Message = new TextFormatter("boatracer.raceFinished.first", ["playerName" => $p_Player->getDisplayName()]);
		else if ($l_PlayerPos == 2)
			$l_Message = new TextFormatter("boatracer.raceFinished.second", ["playerName" => $p_Player->getDisplayName()]);
		else if ($l_PlayerPos == 3)
			$l_Message = new TextFormatter("boatracer.raceFinished.third", ["playerName" => $p_Player->getDisplayName()]);
		else
			$l_Message = new TextFormatter("boatracer.raceFinished.other", ["playerName" => $p_Player->getDisplayName(), "pos" => $l_PlayerPos]);

		foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
		{
			$l_Player->addTitle("", $l_Message->asStringForPlayer($l_Player));
		}

		$this->destroyPlayerBoat($p_Player);

		$p_Player->setGamemode(3);

		if ($l_PlayerPos == GameManager::getInstance()->getPlayerNbrAtStart())
		{
			(new BossbarTimer(150))
				->setTitle(new TextFormatter("timer.returnToLobby"))
				->addStopCallback(function ()
				{
					foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
						LoadBalancer::getInstance()->balancePlayer($l_Player, "lobby");

					new DelayedExec(function ()
					{
						$this->getServer()->shutdown();
					}, 100);
				})
				->start();
		}
	}

	public function applyBoat(Player $p_Player)
	{
		echo "PlayerYaw" . $p_Player->getYaw() . "\n";

		$nbt = new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $p_Player->getX()),
				new DoubleTag("", $p_Player->getY() + 0.1),
				new DoubleTag("", $p_Player->getZ())
			]),
			new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			new ListTag("Rotation", [
				new FloatTag("", $p_Player->getYaw() + 90),
				new FloatTag("", 0)
			]),
		]);

		$entity = Entity::createEntity(BoatEntity::NETWORK_ID, $p_Player->getLevel(), $nbt);
		$entity->spawnToAll();

		if ($entity instanceof Vehicle)
		{
			new DelayedExec(function () use (&$entity, &$p_Player)
			{
				$entity->mountEntity($p_Player);
			});
		}
	}

	public function destroyPlayerBoat(Player $p_Player)
	{
		$l_Boat = $p_Player->vehicle;
		if ($l_Boat != null && $l_Boat instanceof BoatEntity)
			$l_Boat->kill();
	}

	//------------------------
	// EVENTS
	//------------------------
	public function onPlayerJoin(PlayerJoinEvent $p_Event)
	{
		$l_Player = $p_Event->getPlayer();

		$l_Player->sendMessage((new TextFormatter("template.info.template", [
			"gameName" => new TextFormatter("template.br"),
			"text" => new TextFormatter("template.info.br")
		]))->asStringForPlayer($l_Player));

		SpawnManager::getInstance()->getSpawnByName("waiting")->teleport($l_Player);
		if (GameManager::getInstance()->isWaiting())
		{
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

			$l_Player->setGamemode(2);
		} else
		{
			$l_Player->setGamemode(3);
			$l_Player->sendMessage(TextFormat::YELLOW . "You've been automatically set to SPECTATOR");
			$this->getServer()->getLogger()->info($l_Player->getName() . " has been set to SPECTATOR");
		}

		Sidebar::getInstance()->update();
	}

	public function onEntityVehicleExit(EntityVehicleExitEvent $p_Event)
	{
		if ($p_Event->getEntity() instanceof Player && $p_Event->getEntity()->isOp())
			return;

		new DelayedExec(function () use ($p_Event)
		{
			$p_Event->getVehicle()->mountEntity($p_Event->getEntity());
		});
	}

	public function onPlayerExhaust(PlayerExhaustEvent $p_Event)
	{
		$p_Event->setCancelled(true);
	}

	public function onEntityDamage(EntityDamageEvent $p_Event)
	{
		if ($p_Event->getCause() == EntityDamageEvent::CAUSE_VOID)
			echo "Void Damages\n";
		else
			$p_Event->setCancelled(true);
	}

	public function onPlayerQuit(PlayerQuitEvent $p_Event)
	{
		PlayersManager::getInstance()->getFatPlayer($p_Event->getPlayer())->setHasLost();
		$this->destroyPlayerBoat($p_Event->getPlayer());
	}

	public function onPlayerDamage(EntityDamageEvent $e)
	{
		$p = $e->getEntity();
		if ($p instanceof Player)
			$e->setCancelled(true);
	}
}