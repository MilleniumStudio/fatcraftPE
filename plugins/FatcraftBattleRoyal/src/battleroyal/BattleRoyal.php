<?php

namespace battleroyal;

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
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\entity\Effect;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class BattleRoyal extends PluginBase implements Listener
{
	private $m_BattleRoyalConfig;
	private static $m_Instance;
	private $m_WaitingTimer;
	private $m_PlayTimer;

    private $defineZone1 = false;

    private $currentZoneLoc;
    private $currentRadius;

    private $currentDamageLoc = null;
    private $damageRadius = 0;

    private $nextDamageLoc = null;
    private $nextDamageRadius = 0;

    public const TYPE_PLAYER_SPAWN = 0;
    public const TYPE_WORLD_SPAWN = 1;

    public $maxPlayer = 0;

    public static function getInstance(): BattleRoyal
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
		$this->m_BattleRoyalConfig = new BattleRoyalConfig($this->getConfig());
		$this->initialize();
	}

	private function initialize()
	{
		LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);

        WorldUtils::setWorldsTime(2000);
		WorldUtils::stopWorldsTime();

		GameManager::getInstance()->setWaiting(); // init new game on SQL side
        GameManager::getInstance()->m_isBattleRoyal = true;

        FatPlayer::$m_OptionDisplayHealth = false;
        FatPlayer::$m_OptionDisplayNameTag = false;

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

			Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.battleroyal"));

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
			$p_Player->sendMessage((new TextFormatter("template.info.template", [
				"gameName" => new TextFormatter("template.battleroyal"),
				//"text" => new TextFormatter("template.info.hg")
			]))->asStringForPlayer($p_Player));

        if (GameManager::getInstance()->isWaiting())
		{
		    $p_Player->teleport($this->getBattleRoyalConfig()->getWaitingLocation()->asVector3());

		    $p_Player->setGamemode(Player::ADVENTURE);

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

		$this->maxPlayer = count($this->getServer()->getOnlinePlayers());
		// INIT SIDEBAR
		Sidebar::getInstance()->clearLines();

        Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.battleroyal"));

		$this->m_PlayTimer = new DisplayableTimer(GameManager::getInstance()->getPlayingTickDuration());
		$this->m_PlayTimer
			->setTitle(new TextFormatter("timer.playing.title"))
            ->addStartCallback(function()
            {
                $l_centerpoint = new Vector3(rand($this->getBattleRoyalConfig()->getPos1()->x - 150, $this->getBattleRoyalConfig()->getPos1()->x + 150), $this->getBattleRoyalConfig()->getPos1()->y, rand($this->getBattleRoyalConfig()->getPos1()->z - 150, $this->getBattleRoyalConfig()->getPos1()->z + 150));
                $this->setCurrentCenterLoc($l_centerpoint);
                $this->setCurrentRadius($this->getBattleRoyalConfig()->getRadius1());
                $this->computeBubble($this->currentZoneLoc, $this->currentRadius);
                $this->doStuffWithChunks();
                foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                    $l_Player->addTitle("§2Game started !§r", "§8Fly over the place and gear up !§r");
            })
            ->addStopCallback(function ()
			{
                $this->nextStep();
                //$this->endGame();
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

            $l_Player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(10)->setDuration(60 * 20));

			PlayersManager::getInstance()->getFatPlayer($l_Player)->equipKitToPlayer();
            $l_Player->getInventory()->setChestplate(Item::get(ItemIds::ELYTRA));

			$l_Player->addTitle(TextFormat::GREEN . "GO !");

			$l_Player->teleport($this->getBattleRoyalConfig()->getStartGameLocation());

            $l_Player->setGamemode(Player::SURVIVAL);
        }

		// START PLAY TIMER
		$this->m_PlayTimer->start();

		// UNBLOCKING SPAWNS

		Sidebar::getInstance()->update();
	}

	public function needCustomeName(int $p_itemId) : bool
    {
        $value = false;
        switch ($p_itemId)
        {
            case ItemIds::EGG:
            case ItemIds::BOW:
            case ItemIds::ARROW:
            case ItemIds::SNOWBALL:
            case ItemIds::CHORUS_FRUIT_POPPED:
            case ItemIds::ENDER_PEARL:
            case ItemIds::GUNPOWDER:
            case ItemIds::GOLD_BOOTS:
            case ItemIds::GOLD_HELMET:
            case ItemIds::GOLD_CHESTPLATE:
            case ItemIds::GOLD_LEGGINGS:
            case ItemIds::IRON_BOOTS:
            case ItemIds::IRON_HELMET:
            case ItemIds::IRON_CHESTPLATE:
            case ItemIds::IRON_LEGGINGS:
            case ItemIds::CHAINMAIL_BOOTS:
            case ItemIds::CHAINMAIL_HELMET:
            case ItemIds::CHAINMAIL_CHESTPLATE:
            case ItemIds::CHAINMAIL_LEGGINGS:
            case ItemIds::DIAMOND_BOOTS:
            case ItemIds::DIAMOND_HELMET:
            case ItemIds::DIAMOND_CHESTPLATE:
            case ItemIds::DIAMOND_LEGGINGS:
            case ItemIds::WOODEN_SWORD:
            case ItemIds::STONE_SWORD:
            case ItemIds::IRON_SWORD:
            case ItemIds::GOLD_SWORD:
            case ItemIds::DIAMOND_SWORD:
            case ItemIds::GOLDEN_APPLE:
            case ItemIds::LEATHER_HELMET:
            case ItemIds::LEATHER_BOOTS:
            case ItemIds::LEATHER_CHESTPLATE:
            case ItemIds::LEATHER_LEGGINGS:
            $value = true;
        }
        return $value;
}

    public function getBattleRoyalCustomName(int $p_itemId) : string
    {
        switch ($p_itemId)
        {
            case ItemIds::EGG:
                return "§5GRENADE§r";
            case ItemIds::BOW:
                return "§5SNIPER RIFFLE§r";
            case ItemIds::ARROW:
                return "§2SNIPER RIFFLE AMMO§r";
            case ItemIds::SNOWBALL:
                return "§5ASSAULT RIFFLE§r";
            case ItemIds::CHORUS_FRUIT_POPPED:
                return "§2ASSAULT RIFFLE AMMO§r";
            case ItemIds::ENDER_PEARL:
                return "§5SHOTGUN§r";
            case ItemIds::GUNPOWDER:
                return "§2SHOTGUN AMMO§r";
            case ItemIds::GOLD_BOOTS:
                return "§2DESERT RANGERS§r";
            case ItemIds::GOLD_HELMET:
                return "§2DESERT HELMET§r";
            case ItemIds::GOLD_CHESTPLATE:
                return "§2DESERT VEST§r";
            case ItemIds::GOLD_LEGGINGS:
                return "§2DESERT LEGGINGS§r";
            case ItemIds::IRON_BOOTS:
                return "§5FOREST RANGERS REINFORCED §r";
            case ItemIds::IRON_HELMET:
                return "§5FOREST HELMET REINFORCED §r";
            case ItemIds::IRON_CHESTPLATE:
                return "§5FOREST VEST REINFORCED§r";
            case ItemIds::IRON_LEGGINGS:
                return "§5FOREST LEGGINGS REINFORCED §r";
            case ItemIds::CHAINMAIL_BOOTS:
                return "§3FOREST RANGERS§r";
            case ItemIds::CHAINMAIL_HELMET:
                return "§3FOREST HELMET§r";
            case ItemIds::CHAINMAIL_CHESTPLATE:
                return "§3FOREST VEST§e";
            case ItemIds::CHAINMAIL_LEGGINGS:
                return "§3FOREST LEGGINGS§e";
            case ItemIds::DIAMOND_BOOTS:
                return "§4SUPER SOLDIER BOOTS§r";
            case ItemIds::DIAMOND_HELMET:
                return "§4SUPER SOLDIER HELMET§r";
            case ItemIds::DIAMOND_CHESTPLATE:
                return "§4SUPER SOLDIER VEST§r";
            case ItemIds::DIAMOND_LEGGINGS:
                return "§4SUPER SOLDIER LEGGINGS§r";
            case ItemIds::WOODEN_SWORD:
                return "§fBAT§r";
            case ItemIds::STONE_SWORD:
                return "§2NAILED BAT§r";
            case ItemIds::IRON_SWORD:
                return "§3KATANA§r";
            case ItemIds::GOLD_SWORD:
                return "§2MACHETE§r";
            case ItemIds::DIAMOND_SWORD:
                return "§6SUPER KATANA§r";
            case ItemIds::GOLDEN_APPLE:
                return "§5ADRENALINE§r";
            case ItemIds::LEATHER_HELMET;
                return "§fRAMBO HEADBAND§r";
            case ItemIds::LEATHER_BOOTS;
                return "§fFLIPFLOP§r";
            case ItemIds::LEATHER_CHESTPLATE;
                return "§fBEACH TANK TOP§r";
            case ItemIds::LEATHER_LEGGINGS;
                return "§fBEACH SHORT§r";

        }
    }

	public function applyBattleRoyalSidebarTemplate()
    {
        Sidebar::getInstance()
            ->addTranslatedLine(new TextFormatter("template.battleroyal"))
            ->addTranslatedLine(new TextFormatter("template.playfatcraft"))
            ->addTimer($this->m_PlayTimer)
            ->addWhiteSpace()
            ->addMutableLine(function (Player $p_Player)
            {
                $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);
                if ($l_FatPlayer == null)
                    return "";

                $l_pos1 = null;
                $l_radius1 = 0;
                if (!$this->defineZone1)
                {
                    //$l_pos1 = BattleRoyal::getInstance()->getCurrentCenterLoc();
                    //$l_radius1 = BattleRoyal::getInstance()->getCurrentRadius();
                    $l_pos1 = $this->getBattleRoyalConfig()->getPos1();
                    $l_radius1 = $this->getBattleRoyalConfig()->getRadius1();

                }
                else
                {
                    $l_pos1 = BattleRoyal::getInstance()->getCurrentDamageLoc();
                    $l_radius1 = BattleRoyal::getInstance()->getDamageRadius();
                }

                $l_distance = $l_FatPlayer->calcDist($l_pos1);
                if ($l_distance > $l_radius1)
                    return "§4DISTANCE TO SAFE ZONE§r\n" . ($l_distance - $l_radius1) . "m";
                if ($this->getNextDamageLoc() != null && $this->getNextDamageRadius() != 0 && ($l_diff = ($l_FatPlayer->calcDist($this->getNextDamageLoc()) -  $this->getNextDamageRadius())) >= 0)
                    return "§2SAFE ZONE\nNEXT ZONE : " . $l_diff . "m§r";
                else
                    return "§2SAFE ZONE\n";
            })
            ->addWhiteSpace()
            ->addMutableLine(function ()
            {
                return new TextFormatter("hungergame.alivePlayer", ["nbr" => PlayersManager::getInstance()->getInGamePlayerLeft()]);
            });
    }

    private function handlePlayerEffects()
    {
        foreach (PlayersManager::getInstance()->getInGamePlayers() as $l_fatPlayer)
        {
            if ($l_fatPlayer instanceof FatPlayer)
            {
                $l_player = $l_fatPlayer->getPlayer();
                if ($l_fatPlayer->isWithinDist(BattleRoyal::getInstance()->getCurrentDamageLoc(), BattleRoyal::getInstance()->getDamageRadius()))
                {
                    if ($l_player->hasEffect(Effect::CONFUSION))
                        $l_player->removeEffect(Effect::CONFUSION);
                    if ($l_player->hasEffect(Effect::FATAL_POISON))
                        $l_player->removeEffect(Effect::FATAL_POISON);
                }
                else
                {
                    if (!$l_player->hasEffect(Effect::FATAL_POISON))
                        $l_player->addEffect(Effect::getEffect(Effect::FATAL_POISON)->setDuration(INT32_MAX));
                    if (!$l_player->hasEffect(Effect::CONFUSION))
                        $l_player->addEffect(Effect::getEffect(Effect::CONFUSION)->setDuration(INT32_MAX)->setAmplifier(10));
                }
            }
        }
    }

    public function genericStepFirstPart()
    {
        echo ("- generic 1 -\n");

        Sidebar::getInstance()->clearLines();

        $this->m_PlayTimer = new DisplayableTimer(GameManager::getInstance()->getPlayingTickDuration());
        $this->m_PlayTimer
            ->setTitle("NEXT ZONE IN")
            ->addStartCallback(function ()
            {
                // this will set the new direction of COMPASS
                foreach (PlayersManager::getInstance()->getInGamePlayers() as $l_fatPlayer)
                {
                    if ($l_fatPlayer instanceof FatPlayer)
                    {
                        $l_fatPlayer->getPlayer()->setSpawn(BattleRoyal::getInstance()->getCurrentCenterLoc(), BattleRoyal::TYPE_WORLD_SPAWN);
                        echo ("spawn set\n");
                        $l_fatPlayer->getPlayer()->addTitle(
                            ("§5New area defined !§r"),
                            ("Follow your compass to the safe zone"));
                    }
                }


                if ($this->defineZone1)
                {
                    $this->setCurrentDamageLoc($this->getNextDamageLoc());
                    $this->setDamageRadius($this->getNextDamageRadius());
                }

                $this->setNextDamageLoc($this->getCurrentCenterLoc());
                $this->setNextDamageRadius($this->getCurrentRadius());

                $this->applyBattleRoyalSidebarTemplate();
                $this->buildThatWall();
            })
            ->addStopCallback(function ()
            {
                $this->defineZone1 = true;
                $this->nextStep();
            })
            ->addSecondCallback(function ()
            {
                if ($this->currentDamageLoc != null && $this->damageRadius != 0)
                    $this->handlePlayerEffects();
                Sidebar::getInstance()->update();
            });

        $this->temp = microtime(true);
        echo ("start build\n");
        $this->m_PlayTimer->start();
    }

	public function nextStep()
    {
        if ($this->defineZone1 == false)
        {
            echo ("---- zone1 ----\n");
            foreach (PlayersManager::getInstance()->getInGamePlayers() as $l_fatPlayer)
            {
                if ($l_fatPlayer instanceof FatPlayer)
                {
                    $l_Player = $l_fatPlayer->getPlayer();
                    $l_Player->getInventory()->setItem(8, Item::get(ItemIds::COMPASS));
                    if ($l_Player->getInventory()->getChestplate()->getId() == ItemIds::ELYTRA)
                    {
                        $l_Player->getInventory()->removeItem($l_Player->getInventory()->getChestplate());
                    }
                }
            }

            $this->genericStepFirstPart();
            return;
        }

        echo ("---- new Zone ----\n");

        $this->genericStepFirstPart();
    }


    private function setNewRandomGameArea()
    {
        $l_radius = $this->getCurrentRadius();
        $l_loc = $this->getCurrentCenterLoc();

        $l_newRadius = $l_radius * 2 / 3;

        $this->setCurrentCenterLoc(new Vector3(
            rand(($l_loc->x - ($l_radius - $l_newRadius)), ($l_loc->x + ($l_radius - $l_newRadius))),
            $l_loc->y,
            rand(($l_loc->z - ($l_radius - $l_newRadius)), ($l_loc->z + ($l_radius - $l_newRadius)))));
        $this->setCurrentRadius($l_newRadius);
    }

	public function endGame()
    {
        if ($this->m_PlayTimer instanceof Timer)
            $this->m_PlayTimer->cancel();

        $winner = PlayersManager::getInstance()->getInGamePlayers()[0];

        if ($winner instanceof FatPlayer)
        {
            ScoresManager::getInstance()->giveRewardToPlayer($winner->getPlayer()->getUniqueId(), 1);

            GameManager::getInstance()->endGame(false);

            foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                $l_Player->addTitle("Winner is §6" . $winner->getName()."§r !", "Game over");
        }
        else
        {
            GameManager::getInstance()->endGame();
        }

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
				if ($this->m_WaitingTimer instanceof Timer)
                {
                    if ($this->m_WaitingTimer->getTickLeft() > 0 &&
					(count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer()))
                    {
                        $this->m_WaitingTimer->cancel();
                        $this->m_WaitingTimer = null;
                        $this->resetGameWaiting();
                    }
                    else
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
                }

            } else if (GameManager::getInstance()->isPlaying())
			{
			    $nbPlayer = PlayersManager::getInstance()->getInGamePlayerLeft();
				if ($nbPlayer == 1)
				    $this->endGame();
                if ($nbPlayer == 0)
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
			->addTranslatedLine(new TextFormatter("template.battleroyal"))
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
	public function getBattleRoyalConfig(): BattleRoyalConfig
	{
		return $this->m_BattleRoyalConfig;
	}

    public function setCurrentCenterLoc(Vector3 $p_loc)
    {
        $this->currentZoneLoc = $p_loc;
    }

    public function setCurrentRadius(int $p_radius)
    {
        $this->currentRadius = $p_radius;
    }

    public function getCurrentCenterLoc() : Vector3
    {
        return $this->currentZoneLoc;
    }

    public function getCurrentRadius() : int
    {
        return $this->currentRadius;
    }

    public function getCurrentDamageLoc() : Vector3
    {
        return $this->currentDamageLoc;
    }

    public function getDamageRadius() : int
    {
        return $this->damageRadius;
    }

    public function setCurrentDamageLoc(Vector3 $p_damageLoc)
    {
        $this->currentDamageLoc = $p_damageLoc;
    }

    public function setDamageRadius(int $p_damageRadius)
    {
        $this->damageRadius = $p_damageRadius;
    }

    public function getNextDamageLoc() : Vector3
    {
        return $this->nextDamageLoc;
    }

    public function setNextDamageLoc(Vector3 $p_nextDamageLoc): void
    {
        $this->nextDamageLoc = $p_nextDamageLoc;
    }

    public function getNextDamageRadius() : int
    {
        return $this->nextDamageRadius;
    }

    public function setNextDamageRadius(int $p_nextDamageRadius): void
    {
        $this->nextDamageRadius = $p_nextDamageRadius;
    }

    public function nextChunk() : int
    {
        if (count($this->chunkToProcess) <= $this->chunkToProcessItr)
            return -1;
        $this->chunkToProcessItr++;
        return $this->chunkToProcessItr - 1;
    }

    private $bubbleVertices = [];
    private $begin = 0;
    private $chunkToProcess = [];
    private $chunkToProcessItr = 0;

	private function storeVerticesForChunk(Vector3 $p_center, int $p_radius, int $p_x, int $p_z)
    {
        $sqrRadius = pow($p_radius, 2);
        $level = $this->getServer()->getLevel(1);
        $itrX = 0;
        while ($itrX < 16)
        {
            $itrZ = 0;
            while ($itrZ < 16)
            {
                $x = $p_x + $itrX;
                $z = $p_z + $itrZ;

                $sqrDX = pow($p_center->x - $x, 2);
                $sqrDZ = pow($p_center->z - $z, 2);
                $sqrDY = $sqrRadius - $sqrDX - $sqrDZ;

                if ($sqrDY > 0)
                {
                    $dY = sqrt($sqrDY);

                    $y = $p_center->y + $dY;
                    $yLow = $p_center->y - $dY;

                    if ($y > 255)
                        $y = 255;

                    if ($yLow < 0)
                        $yLow = 0;

                    $l_pos = new Position($x, $y, $z);
                    $lBlock = $level->getBlock($l_pos);

                    if ($lBlock->getId() == BlockIds::AIR)
                        $this->bubbleVertices[] = $l_pos;

                    $l_pos = new Position($x, $yLow, $z);
                    $lBlock = $level->getBlock($l_pos);

                    if ($lBlock->getId() == BlockIds::AIR)
                        $this->bubbleVertices[] = $l_pos;
                }
                $itrZ++;
            }
            $itrX++;
        }
    }

    private function computeBubble(Vector3 $p_center, int $p_radius)
    {
        $temp = microtime(true);

        $sideDone["EAST"] = false;
        $sideDone["WEST"] = false;
        $sideDone["NORTH"] = false;
        $sideDone["SOUTH"] = false;

        $chunkOrigin = new Vector3(intval($p_center->x / 16) * 16, 0, intval($p_center->z / 16) * 16);
        $chunkMax =  new Vector3(intval(($p_center->x + $p_radius) / 16) * 16, 0, intval(($p_center->z + $p_radius) / 16) * 16);
        $chunkMin =  new Vector3(intval(($p_center->x - $p_radius) / 16) * 16, 0, intval(($p_center->z - $p_radius) / 16) * 16);

        $itrX = $chunkOrigin->x;
        $itrZ = $chunkOrigin->z;

        $itrCount = 1;

        $sign = 1;

        $this->chunkToProcess[] = new Vector2($itrX, $itrZ);

        while (true)
        {
            $itrQ = 0;
            while ($itrQ < $itrCount)
            {
                $itrX += ($sign * 16);
                $itrQ++;
                $this->chunkToProcess[] = new Vector2($itrX, $itrZ);

                if ($itrX > $chunkMax->x)
                    $sideDone["EAST"] = true;
                if ($itrX < $chunkMin->x)
                    $sideDone["WEST"] = true;
            }
            $itrQ = 0;
            while ($itrQ < $itrCount)
            {
                $itrZ += ($sign * 16);
                $itrQ++;
                $this->chunkToProcess[] = new Vector2($itrX, $itrZ);

                if ($itrZ > $chunkMax->z)
                    $sideDone["NORTH"] = true;
                if ($itrZ < $chunkMin->z)
                    $sideDone["SOUTH"] = true;
            }

            if ($sideDone["EAST"] && $sideDone["WEST"] && $sideDone["NORTH"] && $sideDone["SOUTH"])
                break;

            $sign *= -1;
            $itrCount++;
        }

        $temp = microtime(true) - $temp;
    }

    public $temp;

    public function buildThatWall()
    {
        $level = $this->getServer()->getLevel(1);
        $sizeToProcess = count($this->bubbleVertices) - $this->begin;

        $i = 0;
        while ($sizeToProcess > 0)
        {
            $level->setBlock($this->bubbleVertices[$i + $this->begin],
                    new Block(BlockIds::PORTAL, 3), false, false);
            $i++;
            if ($i >= 2500)
            {
                $this->begin += 2500;

                $task = new SpawnBubbleTask($this);
                $this->getServer()->getScheduler()->scheduleDelayedTask($task, 1);

                return;
            }
            $sizeToProcess--;
        }
        $this->bubbleVertices = array();
        $this->begin = 0;

        $this->chunkToProcessItr = 0;
        $this->chunkToProcess = array();
        $this->temp = microtime(true) - $this->temp;

        echo ("build time " . $this->temp . "\n");
        $this->setNewRandomGameArea();
        $this->computeBubble($this->currentZoneLoc, $this->currentRadius);
        $this->doStuffWithChunks();
    }

    public function doStuffWithChunks()
    {
        $count = 0;
        while ($count < 10)
        {
            if (($chunkIndex = $this->nextChunk()) != -1)
            {
                $this->storeVerticesForChunk($this->getCurrentCenterLoc(), $this->getCurrentRadius(), $this->chunkToProcess[$chunkIndex]->x, $this->chunkToProcess[$chunkIndex]->y);
                $count++;

                continue;
            }
            return;
        }
        $task = new ComputeBubbleTask($this);
        $this->getServer()->getScheduler()->scheduleDelayedTask($task, 1);
    }

}

class SpawnBubbleTask extends PluginTask
{
    public function __construct(Plugin $owner)
    {
        parent::__construct($owner);
    }

    public function onRun(int $currentTick)
    {
        BattleRoyal::getInstance()->buildThatWall();
    }

    public function cancel() {
        $this->getHandler()->cancel();
    }
}

class ComputeBubbleTask extends PluginTask
{
    public function __construct(Plugin $owner)
    {
        parent::__construct($owner);
    }

    public function onRun(int $currentTick)
    {
        BattleRoyal::getInstance()->doStuffWithChunks();
    }

    public function cancel() {
        $this->getHandler()->cancel();
    }
}
