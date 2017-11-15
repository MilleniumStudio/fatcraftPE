<?php
/**
 * Created by Unikaz.
 */

namespace fatcraft\murder;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\scores\ScoresManager;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\tools\schedulers\DisplayableTimer;
use fatutils\tools\ItemUtils;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\schedulers\Timer;
use fatutils\tools\schedulers\TipsTimer;
use fatutils\tools\WorldUtils;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use fatutils\tools\schedulers\BossbarTimer;
use fatutils\ui\WindowsManager;
use MSpawns\Commands\SetAlias;
use MSpawns\Commands\Spawn;
use pocketmine\block\BlockIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;

class Murder extends PluginBase implements Listener
{
    const DEBUG = false;
    private static $m_Instance;
    private $m_MurderConfig;
    private $m_WaitingTimer;
    private $m_PlayTimer;

    private $m_murdererUUID;
    private $m_playersKilled = 0;


    public static function getInstance(): Murder
    {
        return self::$m_Instance;
    }

    public function onLoad()
    {
        self::$m_Instance = $this;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getCommand("murder")->setExecutor(self::$m_Instance);

        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        $this->m_MurderConfig = new MurderConfig($this->getConfig());
        $this->initialize();
    }

    private function initialize()
    {
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);
//        PlayersManager::getInstance()->displayHealth();
        WorldUtils::stopWorldsTime();
        WorldUtils::setWorldsTime(15000); // = 12h * 3600 seconds * 20 ticks (day = 864000)

		$this->m_WaitingTimer = new BossbarTimer(GameManager::getInstance()->getWaitingTickDuration());
		$this->m_WaitingTimer
			->setTitle(new TextFormatter("timer.waiting.title"))
			->addStopCallback(function () {
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
			->clearLines()
			->addTranslatedLine(new TextFormatter("murder.sidebar.title"))
			->addTimer($this->m_WaitingTimer)
			->addWhiteSpace()
			->addMutableLine(function () {
				return new TextFormatter("game.waitingForMore", ["amount" => max(0, PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers()))]);
			});
    }

    public function handlePlayerConnection(PlayerJoinEvent $p_event)
    {
        $p_Player = $p_event->getPlayer();

		$p_Player->sendMessage((new TextFormatter("template.info.template", [
			"gameName" => new TextFormatter("template.md"),
			"text" => new TextFormatter("template.info.md")
		]))->asStringForPlayer($p_Player));

        if (GameManager::getInstance()->isWaiting()) {

            $p_Player->setGamemode(Player::ADVENTURE);

            if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMaxPlayer()) {
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
//            else if (count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer()) {
//                $l_WaitingFor = PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers());
//                foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
//					$l_Player->addTitle("", (new TextFormatter("game.waitingForMore", ["amount" => $l_WaitingFor]))->asStringForPlayer($l_Player), 1, 60, 1);
//            }
        }

        Sidebar::getInstance()->update();
    }


    //---------------------
    // UTILS
    //---------------------
    public function startGame()
    {
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);
		GameManager::getInstance()->startGame();

        // Clear windows registry to avoid having player choosing team after start
        WindowsManager::getInstance()->clearRegistry();

		// INIT PLAY TIMER
		$this->m_PlayTimer = new DisplayableTimer(GameManager::getInstance()->getPlayingTickDuration());
		$this->m_PlayTimer
			->setTitle(new TextFormatter("timer.playing.title"))
			->addStartCallback(function () {
				FatUtils::getInstance()->getLogger()->info("Playing timer starts !");

				foreach (Server::getInstance()->getOnlinePlayers() as $player) {
					$player->getInventory()->setHeldItemIndex(2);
				}

				// random murderer
				/** @var Player $murderer */
				$murderer = Server::getInstance()->getOnlinePlayers()[array_rand(Server::getInstance()->getOnlinePlayers())];
				print_r($murderer);
				$this->m_murdererUUID = $murderer->getUniqueId();
				$murderer->getInventory()->addItem(Item::get(ItemIds::IRON_SWORD));
				$murderer->sendMessage("You are the MURDERER !");

				//random cop
				if (count(Server::getInstance()->getOnlinePlayers()) > 1) {
					$cop = null;
					do {
						/** @var Player $cop */
						$cop = Server::getInstance()->getOnlinePlayers()[array_rand(Server::getInstance()->getOnlinePlayers())];
					} while ($cop->getUniqueId()->equals($murderer->getUniqueId()));

					$cop->getInventory()->addItem(Item::get(ItemIds::BOW));
					$cop->sendMessage("You got a gun !");
				}

				Sidebar::getInstance()->update();
			})
			->addTickCallback([$this, "onPlayingTick"])
			->addStopCallback(function () {
				$this->endGame();
			});

        // INIT SIDEBAR
		Sidebar::getInstance()
			->clearLines()
			->addTranslatedLine(new TextFormatter("murder.sidebar.title"))
			->addTimer($this->m_PlayTimer)
			->addWhiteSpace()
			->addMutableLine(function (Player $player) {
				return (new TextFormatter("murder.nbAlivedPlayers"))->addParam("nb", PlayersManager::getInstance()->getInGamePlayerLeft() - 1)->asStringForPlayer($player);
			})
			->addMutableLine(function (Player $player) {
				return ($this->m_murdererUUID != null && $this->m_murdererUUID->equals($player->getUniqueId())) ? (new TextFormatter("murder.youarethemurderer"))->addParam("nb", PlayersManager::getInstance()->getInGamePlayerLeft())->asStringForPlayer($player) : "";
			});

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            SpawnManager::getInstance()->getRandomEmptySpawn()->teleport($player);
        }

		$this->m_PlayTimer->start();
    }

    public function onPlayingTick()
    {
        if (Server::getInstance()->getTick() % 80 == 0) {
            /** @var FatPlayer $fPlayer */
            foreach (PlayersManager::getInstance()->getInGamePlayers() as $fPlayer) {
                $hasBow = false;
                $hasArrow = false;
                if (!$fPlayer->getPlayer()->isConnected())
                    continue;
                foreach ($fPlayer->getPlayer()->getInventory()->getContents() as $item) {
                    if ($item->getId() == ItemIds::ARROW) {
                        $hasArrow = true;
                        break;
                    } else if ($item->getId() == ItemIds::BOW) {
                        $hasBow = true;
                    }
                }
                if ($hasBow && !$hasArrow) {
                    $fPlayer->getPlayer()->getInventory()->addItem(Item::get(ItemIds::ARROW));
                }
            }
        }
        if (Server::getInstance()->getTick() % 200 == 0) {
            if (rand(0, 100) > 50) {
                /** @var Location $loc */
                $loc = $this->getMurderConfig()->gunPartsLocs[array_rand($this->getMurderConfig()->gunPartsLocs)];
                $loc->level->dropItem($loc->asVector3(), Item::get(ItemIds::IRON_INGOT));
                echo "DROP !\n";
            }
        }
    }

    public function endGameMurderer()
    {
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
            $l_Player->addTitle(
                (new TextFormatter("murder.murderWin"))->asStringForPlayer($l_Player),
                (new TextFormatter("game.winner.single"))->addParam("name", PlayersManager::getInstance()->getFatPlayerByUUID($this->m_murdererUUID)->getName())->asStringForPlayer($l_Player),
                30, 100, 30);
        }
        //rewards
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->getUniqueId()->equals($this->m_murdererUUID))
				ScoresManager::getInstance()->giveRewardToPlayer($player->getUniqueId(), 1);
            else
				ScoresManager::getInstance()->giveRewardToPlayer($player->getUniqueId(), 0.3);
        }

        $this->endGame();
    }

    public function endGameLambdas(player $killer)
    {
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
            $l_Player->addTitle(
                (new TextFormatter("murder.lambdasWin"))->asStringForPlayer($l_Player),
                (new TextFormatter("murder.lambdasWin.named"))->addParam("name", $killer->getName())->asStringForPlayer($l_Player),
                30, 100, 30);
        }
        //rewards
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->getUniqueId()->equals($this->m_murdererUUID))
				ScoresManager::getInstance()->giveRewardToPlayer($player->getUniqueId(), 0.3 + ($this->m_playersKilled * 0.1));
            else if ($player->getUniqueId()->equals($killer->getUniqueId()))
				ScoresManager::getInstance()->giveRewardToPlayer($player->getUniqueId(), 1.1);
            else if ($player->getGamemode() == Player::SPECTATOR)
            	ScoresManager::getInstance()->giveRewardToPlayer($player->getUniqueId(), 0.5);
            else
				ScoresManager::getInstance()->giveRewardToPlayer($player->getUniqueId(), 1);
        }

        $this->endGame();
    }

    public function endGame()
    {
        if ($this->m_PlayTimer instanceof Timer)
            $this->m_PlayTimer->cancel();

        (new TipsTimer(150))
            ->setTitle(new TextFormatter("timer.returnToLobby"))
            ->addStopCallback(function () {
                foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
                    LoadBalancer::getInstance()->balancePlayer($l_Player, LoadBalancer::TEMPLATE_TYPE_LOBBY);
                }
            })
            ->start();

        (new Timer(200))
            ->addStopCallback(function () {
                $this->getServer()->shutdown();
            })
            ->start();

        GameManager::getInstance()->endGame();
    }

    //---------------------
    // GETTERS
    //---------------------
    /**
     * @return mixed
     */
    public function getMurderConfig(): MurderConfig
    {
        return $this->m_MurderConfig;
    }

    //---------------------
    // Event
    //---------------------
    // Remove Hunger
    public function onPlayerExhaust(PlayerExhaustEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onPlayerRespawn(PlayerRespawnEvent $p_Event)
    {

    }

    public function onPlayerDamage(EntityDamageEvent $p_event)
    {
        if (GameManager::getInstance()->isWaiting() && $p_event->getCause() !== EntityDamageEvent::CAUSE_VOID)
            $p_event->setCancelled(true);
        else if ($p_event instanceof EntityDamageByEntityEvent) {
            $damager = $p_event->getDamager();
            if ($damager instanceof Player) {
                $item = $damager->getInventory()->getItemInHand();
                if ($item->getId() == ItemIds::IRON_SWORD) {
                    $p_event->setDamage(2000);
                    return;
                } else if ($item->getId() == ItemIds::BOW && $p_event instanceof EntityDamageByChildEntityEvent) {
                    // someone was killed by a gunner
                    $target = $p_event->getEntity();
                    if ($target instanceof Player) {
                        if (!$target->getUniqueId()->equals($this->m_murdererUUID)) {
                            // gunner killed a citizen
                            $p_event->setCancelled(true);
                            $damager->sendMessage((new TextFormatter("murder.killInnocent"))->asStringForPlayer($damager));
                            $damager->kill();
                            return;
                        }
                    }
                    $p_event->setDamage(2000);
                    return;
                }
            }
        }
        $p_event->setCancelled(true);

    }

    public function onPlayerQuit(PlayerQuitEvent $p_Event)
    {
		if (GameManager::getInstance()->isPlaying())
		{
			PlayersManager::getInstance()->getFatPlayer($p_Event->getPlayer())->setOutOfGame();
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
				}
			} else if (GameManager::getInstance()->isPlaying())
			{
				if (count($this->getServer()->getOnlinePlayers()) == 0)
					$this->getServer()->shutdown();
			}
		}, 1);
    }

    /**
     * @param PlayerDeathEvent $e
     */
    public function playerDeathEvent(PlayerDeathEvent $e)
    {
		if (GameManager::getInstance()->isPlaying())
		{
			$p = $e->getEntity();
			PlayersManager::getInstance()->getFatPlayer($p)->setOutOfGame();

			$customDeathMessage = "";

			$killer = null;
			$lastDamageEvent = $p->getLastDamageCause();
			if ($lastDamageEvent instanceof EntityDamageByEntityEvent)
			{
				/** @var Player $killer */
				$killer = $lastDamageEvent->getDamager();
			}

			//if it's the murderer
			if ($p->getUniqueId()->equals($this->m_murdererUUID))
			{
				$customDeathMessage = $p->getName() . " était le meurtrier et a été tué par " . $killer->getName();
				// endGame, lambdas win
				$this->endGameLambdas($killer);
			} else
			{
				$customDeathMessage = $p->getName() . " a été tué";
				if (PlayersManager::getInstance()->getInGamePlayerLeft() <= 1)
				{
					$this->m_playersKilled++;
					// endgame, murderer wins
					$this->endGameMurderer();
				} else if ($killer == null || $killer->getUniqueId()->equals($this->m_murdererUUID))
				{
					// else heu... the game continue ^^
					$this->m_playersKilled++;
				} else
				{
					$killer->sendMessage("You kill an innocent !");
					$killer->kill();
				}
			}
			$e->setDeathMessage($customDeathMessage);

			$p->setGamemode(3);
			Sidebar::getInstance()->update();
		}
    }

    public function onArrowHit(ProjectileHitEvent $p_event)
    {
        $p_event->getEntity()->kill();
    }

    public function onLoot(InventoryPickupItemEvent $p_event)
    {
        $holder = $p_event->getInventory()->getHolder();
        if ($holder instanceof Player) {
            if (!$holder->getUniqueId()->equals($this->m_murdererUUID)) {
                if ($p_event->getItem()->getItem()->getId() == ItemIds::IRON_INGOT) {

                    new DelayedExec(function () use ($p_event)
					{ //delayed to allow the loot of the ingot before making this computation
						$nbIngots = 0;
						foreach ($p_event->getInventory()->getContents() as $content)
						{
							if ($content->getId() == ItemIds::IRON_INGOT)
								$nbIngots += $content->getCount();
						}
						if ($nbIngots >= 6)
						{
							$nbRemove = 6;
							foreach ($p_event->getInventory()->getContents() as $content)
							{
								if ($content->getId() == ItemIds::IRON_INGOT)
								{
									if ($content->getCount() >= $nbRemove)
									{
										$content->setCount($content->getCount() - $nbRemove);
										break;
									} else
									{
										$nbRemove -= $content->getCount();
										$content->setCount(0);
									}
								}
							}
							$p_event->getInventory()->addItem(Item::get(ItemIds::BOW));
							$p_event->getInventory()->sendContents($p_event->getInventory()->getHolder());
						}
					}, 1);
                }
            } else {
                $p_event->setCancelled(true);
            }
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        $player = null;
        if ($sender instanceof Player) {
            $player = $sender;
        } else {
            echo "sender is not a player\n";
        }

        if (Murder::DEBUG) {
            $firstSwitch = true;
            switch ($args[0]) {

                default:
                    $firstSwitch = false;
            }
            if ($firstSwitch)
                return true;
        }


        if (!$sender->isOp()) {
            $sender->sendMessage("you need to be op");
            return false;
        }

        switch ($args[0]) {
            case "npc": {
                $tag = new CompoundTag("", [
                        "Pos" => new ListTag("Pos", [
                            new DoubleTag("", $player->getLocation()->getX()),
                            new DoubleTag("", $player->getLocation()->getY()),
                            new DoubleTag("", $player->getLocation()->getZ())
                        ]),
                        "Motion" => new ListTag("Motion", [
                            new DoubleTag("", 0),
                            new DoubleTag("", 0),
                            new DoubleTag("", 0)
                        ]),
                        "Rotation" => new ListTag("Rotation", [
                            new FloatTag("", 90),
                            new FloatTag("", 0)
                        ])
                    ]
                );
                $villager = new Villager($player->getLocation()->level, $tag);
                $player->getLocation()->getLevel()->addEntity($villager);
                $villager->spawnToAll();
            }
                break;
        }
        return true;
    }
}
