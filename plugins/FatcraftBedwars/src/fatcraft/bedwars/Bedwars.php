<?php

namespace fatcraft\bedwars;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\scores\ScoresManager;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use fatutils\tools\schedulers\BossbarTimer;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\tools\schedulers\DisplayableTimer;
use fatutils\tools\ItemUtils;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\schedulers\Timer;
use fatutils\tools\WorldUtils;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use fatutils\tools\schedulers\TipsTimer;
use fatutils\ui\WindowsManager;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;

class Bedwars extends PluginBase implements Listener
{
    const DEBUG = false;
    const CONFIG_KEY_FORGES_ROOT = "forges";
    const CONFIG_KEY_FORGES_LOCATION = "location";
    const CONFIG_KEY_FORGES_ITEM_TYPE = "itemType";
    const CONFIG_KEY_FORGES_POP_DELAY = "popDelay";
    const CONFIG_KEY_FORGES_POP_AMOUNT = "popAmount";
    const CONFIG_KEY_FORGES_TEAM = "team";
    const CONFIG_KEY_NPC_SHOP = "npcShop";

    const PLAYER_DATA_CURRENCY_IRON = "currency.iron";
    const PLAYER_DATA_CURRENCY_GOLD = "currency.gold";
    const PLAYER_DATA_CURRENCY_DIAMOND = "currency.diamond";

    private $bedBlockId;
    private $sidebarTitle;

    private $m_BedwarsConfig;
    private static $m_Instance;
    private $m_WaitingTimer;
    private $m_PlayTimer;
    private $m_secondsSinceStart = 0;
    private $m_timeTier;

    private $m_Forges = [];

    public static function getInstance(): Bedwars
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

        $this->getCommand("bw")->setExecutor(self::$m_Instance);

        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        $this->m_BedwarsConfig = new BedwarsConfig($this->getConfig());
        $this->initialize();
    }

    private function initialize()
    {
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);
        WorldUtils::stopWorldsTime();

        // FORGE CONFIG LOADING
        if ($this->getConfig()->exists(TeamsManager::CONFIG_KEY_TEAM_ROOT)) {
            FatUtils::getInstance()->getLogger()->info("FORGES loading...");
            foreach ($this->getConfig()->get(Bedwars::CONFIG_KEY_FORGES_ROOT) as $key => $value) {
                if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_LOCATION, $value)) {
                    $newForge = new Forge(WorldUtils::stringToLocation($value[Bedwars::CONFIG_KEY_FORGES_LOCATION]));

                    if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_ITEM_TYPE, $value))
                        $newForge->setItemType(ItemUtils::getItemIdFromName($value[Bedwars::CONFIG_KEY_FORGES_ITEM_TYPE]));

                    if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_POP_DELAY, $value)) {
                        $index = 0;
                        foreach ($value[Bedwars::CONFIG_KEY_FORGES_POP_DELAY] as $delay) {
                            $newForge->setPopDelay($index, $delay * 20);
                            $index++;
                        }
                    }

                    if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_TEAM, $value)) {
                        $newForge->setTeam($value[Bedwars::CONFIG_KEY_FORGES_TEAM]);
                    }

                    FatUtils::getInstance()->getLogger()->info("   - " . $key);
                    $this->m_Forges[] = $newForge;
                }
            }
        }

        $this->bedBlockId = BlockIds::BEACON;
        if ($this->getBedwarsConfig()->isFastRush())
        {
            $this->sidebarTitle = "fastRush.sidebar.title";
            $this->bedBlockId = BlockIds::BED_BLOCK;
        }
        else
            $this->sidebarTitle = "bedwars.sidebar.title";
        $this->m_WaitingTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration());
        $this->m_WaitingTimer
            ->setTitle(new TextFormatter("timer.waiting.title"))
            ->addStopCallback(function () {
                $this->startGame();
            })
            ->addSecondCallback(function () {
                if ($this->m_WaitingTimer instanceof Timer) {
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
//            ->setUpdateTickInterval(40)
            ->addTranslatedLine(new TextFormatter($this->sidebarTitle))
            ->addTimer($this->m_WaitingTimer)
            ->addWhiteSpace()
            ->addMutableLine(function () {
                return new TextFormatter("game.waitingForMore", ["amount" => max(0, PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers()))]);
            });
    }

    public function handlePlayerConnection(Player $p_Player)
    {
        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

        $line = "template.bw";
        $line2 = "template.info.bw";

        if ($this->getBedwarsConfig()->isFastRush())
        {
            $line = "template.fastRush";
            $line2 = "template.info.fastRush";
        }

        $p_Player->sendMessage((new TextFormatter("template.info.template", [
            "gameName" => new TextFormatter($line),
            "text" => new TextFormatter($line2)
        ]))->asStringForPlayer($p_Player));

        if (GameManager::getInstance()->isWaiting()) {
            $l_Team = TeamsManager::getInstance()->addInBestTeam($p_Player);
            if (!is_null($l_Team) && !is_null($l_Team->getSpawn())) {
//                $l_Team->getSpawn()->teleport($p_Player, 3);
                $p_Player->setGamemode(Player::ADVENTURE);

                new DelayedExec(function () use ($p_Player, $l_Team) {
                    $p_Player->addTitle("", (new TextFormatter("player.team.join", ["teamName" => $l_Team->getColoredName()]))->asStringForPlayer($p_Player));
                    if (!Bedwars::getInstance()->getBedwarsConfig()->isFastRush())
                        $p_Player->sendMessage((new TextFormatter("bedwars.team.chooseYourTeam"))->asStringForPlayer($p_Player));
                }, 2);

                if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMaxPlayer()) {
                    $this->getLogger()->info("MAX PLAYER REACH !");
                    /*if ($this->m_WaitingTimer instanceof Timer)
                        $this->m_WaitingTimer->cancel();*/
                    //$this->startGame();
                }
                if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer()) {
                    $this->getLogger()->info("MIN PLAYER REACH !");
                    if ($this->m_WaitingTimer instanceof Timer)
                        $this->m_WaitingTimer->start();
                }
//                else if (count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer())
//                {
//                    $l_WaitingFor = PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers());
//                    foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
//                        $l_Player->addTitle("", (new TextFormatter("game.waitingForMore", ["amount" => $l_WaitingFor]))->asStringForPlayer($l_Player), 1, 60, 1);
//                }
            }
            Sidebar::getInstance()->update();
        } else {
            //LoadBalancer::getInstance()->balancePlayer($p_Player, LoadBalancer::TEMPLATE_TYPE_LOBBY);
            //$p_Player->setGamemode(3);
            /*$p_Player->sendMessage((new TextFormatter("player.autoSwitchToSpec"))->asStringForFatPlayer($l_FatPlayer));
            $this->getServer()->getLogger()->info($p_Player->getName() . " has been automatically set to SPECTATOR");*/
        }

    }

    //---------------------
    // CURRENCIES
    //---------------------
    public function setPlayerIron(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->setData(Bedwars::PLAYER_DATA_CURRENCY_IRON, $p_Value);
        Sidebar::getInstance()->updatePlayer($p_Player);
    }

    public function setPlayerGold(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->setData(Bedwars::PLAYER_DATA_CURRENCY_GOLD, $p_Value);
        Sidebar::getInstance()->updatePlayer($p_Player);
    }

    public function setPlayerDiamond(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->setData(Bedwars::PLAYER_DATA_CURRENCY_DIAMOND, $p_Value);
        Sidebar::getInstance()->updatePlayer($p_Player);
    }

    public function modPlayerIron(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->addData(Bedwars::PLAYER_DATA_CURRENCY_IRON, $p_Value);
        Sidebar::getInstance()->updatePlayer($p_Player);
    }

    public function modPlayerGold(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->addData(Bedwars::PLAYER_DATA_CURRENCY_GOLD, $p_Value);
        Sidebar::getInstance()->updatePlayer($p_Player);
    }

    public function modPlayerDiamond(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->addData(Bedwars::PLAYER_DATA_CURRENCY_DIAMOND, $p_Value);
        Sidebar::getInstance()->updatePlayer($p_Player);
    }

    public function getPlayerIron(Player $p_Player): int
    {
        if (PlayersManager::getInstance()->getFatPlayer($p_Player) == null)
            return 0;
        return PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->getData(Bedwars::PLAYER_DATA_CURRENCY_IRON, 0);
    }

    public function getPlayerGold(Player $p_Player): int
    {
        if (PlayersManager::getInstance()->getFatPlayer($p_Player) == null)
            return 0;
        return PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->getData(Bedwars::PLAYER_DATA_CURRENCY_GOLD, 0);
    }

    public function getPlayerDiamond(Player $p_Player): int
    {
        if (PlayersManager::getInstance()->getFatPlayer($p_Player) == null)
            return 0;
        return PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->getData(Bedwars::PLAYER_DATA_CURRENCY_DIAMOND, 0);
    }

    public function getIronForgeLevel(Team $p_team): int
    {
        foreach ($this->m_Forges as $forge) {
            if ($forge instanceof Forge && $forge->getTeam() != null && $forge->getTeam() == $p_team->getName()) {
                return $forge->getLevel();
            }
        }
        return -1;
    }

    public function upgradeIronForge(Team $p_team): bool
    {
        /** @var Forge $forge */
        foreach ($this->m_Forges as $forge) {
            if ($forge->getTeam() != null && $forge->getTeam() == $p_team->getName()) {
                return $forge->upgrade();
            }
        }
        return false;
    }

    public function upgradeGoldForges()
    {
        /** @var Forge $forge */
        foreach ($this->m_Forges as $forge) {
            if ($forge->getItemType() == ItemIds::GOLD_INGOT) {
                $forge->upgrade();
            }
        }
    }

    public function upgradeDiamondForges()
    {
        /** @var Forge $forge */
        foreach ($this->m_Forges as $forge) {
            if ($forge->getItemType() == ItemIds::DIAMOND) {
                $forge->upgrade();
            }
        }
    }

    //---------------------
    // UTILS
    //---------------------
    public function startGame()
    {
        // CLOSING SERVER
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);

        $this->getServer()->getScheduler()->scheduleDelayedTask(new class(Bedwars::getInstance()) extends PluginTask
        {
            public function __construct(PluginBase $p_Plugin)
            {
                parent::__construct($p_Plugin);
            }

            public function onRun(int $currentTick)
            {
                GameManager::getInstance()->startGame();
            }
        }, 5);


        // INIT PLAY TIMER
        $this->m_PlayTimer = new DisplayableTimer(GameManager::getInstance()->getPlayingTickDuration());
        $this->m_PlayTimer
            ->setTitle(new TextFormatter("timer.playing.title"))
            ->addStartCallback(function () {
                FatUtils::getInstance()->getLogger()->info("Game end timer starts !");
                FatUtils::getInstance()->getLogger()->info("Forges are heating up !");

                Server::getInstance()->broadcastMessage("Team Balance...");
                TeamsManager::getInstance()->balanceTeams();

                $l_GoMsgFormatter = new TextFormatter("game.start");
                foreach ($this->getServer()->getOnlinePlayers() as $l_Player) {
                    $l_Team = TeamsManager::getInstance()->getPlayerTeam($l_Player);

                    if ($l_Team instanceof Team) {
                        PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
                        $l_Player->setGamemode(Player::SURVIVAL);
                        $l_Team->getSpawn()->teleport($l_Player, 2);

                        $l_Player->getArmorInventory()->setChestplate(ItemUtils::getColoredItemIfColorable(Item::get(ItemIds::LEATHER_CHESTPLATE), $l_Team->getColor()));
                        $l_Player->getArmorInventory()->setLeggings(ItemUtils::getColoredItemIfColorable(Item::get(ItemIds::LEATHER_LEGGINGS), $l_Team->getColor()));
                        $l_Player->getInventory()->addItem(Item::get(ItemIds::WOODEN_SWORD));
                        if ($this->getBedwarsConfig()->isFastRush()) {
                            $l_Player->getInventory()->addItem(Item::get(ItemIds::STONE_PICKAXE));
                            $goldenApple = Item::get(ItemIds::GOLDEN_APPLE);
                            $goldenApple->setCount(5);
                            $l_Player->getInventory()->addItem($goldenApple);
                            $tnt = Item::get(ItemIds::TNT);
                            $tnt->setCount(5);
                            $l_Player->getInventory()->addItem($tnt);
                            $l_Player->getInventory()->addItem(Item::get(ItemIds::FLINT_AND_STEEL));
                            $sandStone = Item::get(ItemIds::SANDSTONE);
                            $sandStone->setCount(30);
                            $l_Player->getInventory()->addItem($sandStone);
                        }
                        //PlayersManager::getInstance()->getFatPlayer($l_Player)->equipKitToPlayer();

                        $l_Player->addTitle($l_GoMsgFormatter->asStringForPlayer($l_Player));
                    }
                }

                Sidebar::getInstance()->update();
            })
            ->addTickCallback([$this, "onPlayingTick"])
            ->addStopCallback(function () {
                if (TeamsManager::getInstance()->getInGameTeamNbr() <= 1 && !Bedwars::DEBUG)
                    $this->endGame();
                else {
                    foreach (TeamsManager::getInstance()->getTeams() as $team) {
                        $bedLoc = $this->getBedwarsConfig()->getBedLocation($team);
                        $bedLoc->level->setBlockIdAt($bedLoc->getFloorX(), $bedLoc->getFloorY(), $bedLoc->getFloorZ(), BlockIds::AIR);
                    }
                    Sidebar::getInstance()->update();
                    foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                        $l_Player->addTitle((new TextFormatter("bedwars.deathmatch.title"))->asStringForPlayer($l_Player), (new TextFormatter("bedwars.deathmatch.subtitle"))->asStringForPlayer($l_Player));
                }
            });

        // INIT SIDEBAR
        Sidebar::getInstance()->clearLines();
        Sidebar::getInstance()
            ->addTranslatedLine(new TextFormatter($this->sidebarTitle))
            ->addTimer($this->m_PlayTimer)
            ->addWhiteSpace()
            ->addTranslatedLine(new TextFormatter("bedwars.sidebar.teams.title"))
            ->addMutableLine(function () {
                $l_Ret = [];

                foreach (TeamsManager::getInstance()->getTeams() as $l_Team) {
                    if ($l_Team instanceof Team) {
                        $l_State = "";
                        $bedLocation = $this->getBedwarsConfig()->getBedLocation($l_Team);
                        if (Server::getInstance()->getDefaultLevel()->getBlockIdAt($bedLocation->getFloorX(), $bedLocation->getFloorY(), $bedLocation->getFloorZ()) == $this->bedBlockId)
                            $l_State = TextFormat::GREEN . "OK";
                        else {
                            $l_AliveTeamPlayer = $l_Team->getInGamePlayerLeft();
                            if ($l_AliveTeamPlayer > 0)
                                $l_State = TextFormat::AQUA . $l_AliveTeamPlayer;
                            else
                                $l_State = TextFormat::RED . "X";
                        }

                        $l_Ret[] = $l_Team->getColoredName() . TextFormat::WHITE . " : " . $l_State;
                    }
                }

                return $l_Ret;
            })
            ->addWhiteSpace()
            ->addTranslatedLine(new TextFormatter("bedwars.sidebar.currencies.title"))
            ->addMutableLine(function (Player $p_Player) {
                if (Bedwars::getInstance()->getBedwarsConfig()->isFastRush()) {
                    return [
                        new TextFormatter("bedwars.sidebar.currency.iron", ["amount" => $this->getPlayerIron($p_Player)]),
                        new TextFormatter("bedwars.sidebar.currency.gold", ["amount" => $this->getPlayerGold($p_Player)]),
                    ];
                }
                return [
                    new TextFormatter("bedwars.sidebar.currency.iron", ["amount" => $this->getPlayerIron($p_Player)]),
                    new TextFormatter("bedwars.sidebar.currency.gold", ["amount" => $this->getPlayerGold($p_Player)]),
                    new TextFormatter("bedwars.sidebar.currency.diamond", ["amount" => $this->getPlayerDiamond($p_Player)])
                ];
            });

        //remove team selectors
        TeamsManager::getInstance()->clearNPCs();

        // Clear windows registry to avoid having player choosing team after start
        WindowsManager::getInstance()->clearRegistry();

        //load shops
        foreach (FatUtils::getInstance()->getTemplateConfig()->get(Bedwars::CONFIG_KEY_NPC_SHOP) as $value)
            new ShopKeeper(WorldUtils::stringToLocation($value));

        $this->m_timeTier = (int)(GameManager::getInstance()->getPlayingTickDuration() / 60);

        if ($this->m_PlayTimer instanceof Timer)
            $this->m_PlayTimer->start();
    }

    public function onPlayingTick()
    {
        if ($this->getServer()->getTick() % 20 == 0) {
            foreach ($this->m_Forges as $l_Forge) {
                if ($l_Forge instanceof Forge) {
                    if ($l_Forge->canPop())
                        $l_Forge->pop();
                }
            }
            $this->m_secondsSinceStart++;
            if ($this->m_secondsSinceStart == $this->m_timeTier || $this->m_secondsSinceStart == $this->m_timeTier * 2) {
                $this->upgradeGoldForges();
                $this->upgradeDiamondForges();
            }
        }
    }

    public function endGame()
    {
        if ($this->m_PlayTimer instanceof Timer)
            $this->m_PlayTimer->cancel();

        GameManager::getInstance()->endGame();

        $winnerTeams = TeamsManager::getInstance()->getInGameTeams();
        $winnerName = "";
        if (count($winnerTeams) > 0) {
            $winnerTeam = $winnerTeams[0];
            if ($winnerTeam instanceof Team) {
                $winnerName = $winnerTeam->getColoredName();
                foreach ($winnerTeam->getPlayersUuid() as $l_Uuid)
                    ScoresManager::getInstance()->giveRewardToPlayer($l_Uuid, 1);
            }
        }

        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
            $l_Player->addTitle(
                (new TextFormatter("game.end"))->asStringForPlayer($l_Player),
                (new TextFormatter("game.winner.team.single"))->addParam("name", $winnerName)->asStringForPlayer($l_Player),
                30, 100, 30);
        }

        (new BossbarTimer(150))
            ->setTitle(new TextFormatter("timer.returnToLobby"))
            ->addStopCallback(function () {
                foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
                    LoadBalancer::getInstance()->balancePlayer($l_Player, LoadBalancer::TEMPLATE_TYPE_LOBBY);

                new DelayedExec(function () {
                    $this->getServer()->shutdown();
                }, 100);
            })
            ->start();
    }

    //---------------------
    // GETTERS
    //---------------------
    /**
     * @return mixed
     */
    public function getBedwarsConfig(): BedwarsConfig
    {
        return $this->m_BedwarsConfig;
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
        $l_PlayerTeam = TeamsManager::getInstance()->getPlayerTeam($p_Event->getPlayer());

        if (!is_null($l_PlayerTeam) && !is_null($l_PlayerTeam->getSpawn()))
            $p_Event->setRespawnPosition($l_PlayerTeam->getSpawn()->getLocation());
    }

    public function onPlayerDamage(EntityDamageEvent $p_Event)
    {
        if (GameManager::getInstance()->isWaiting() && $p_Event->getCause() !== EntityDamageEvent::CAUSE_VOID) {
            $p_Event->setCancelled(true);
            return;
        }
        $entity = $p_Event->getEntity();
        if ($entity instanceof Player) {
            if ($this->getBedwarsConfig()->isFastRush()) {
                if ($entity->getHealth() - $p_Event->getFinalDamage() <= 0) {
                    $p_Event->setCancelled();

                    $team = PlayersManager::getInstance()->getFatPlayer($entity)->getTeam();
                    $bedLoc = $this->getBedwarsConfig()->getBedLocation($team);

                    if ($bedLoc != null)
                    {

                        if ($bedLoc->getLevel()->getBlockIdAt($bedLoc->getFloorX(), $bedLoc->getFloorY(), $bedLoc->getFloorZ()) == $this->bedBlockId)
                        {
                            $this->getServer()->getScheduler()->scheduleDelayedTask(new class(Bedwars::getInstance(), $entity) extends PluginTask
                            {
                                private $m_player;

                                public function __construct(PluginBase $p_Plugin, Player $p_player)
                                {
                                    parent::__construct($p_Plugin);
                                    $this->m_player = $p_player;
                                }

                                public function onRun(int $currentTick)
                                {
                                    $team = PlayersManager::getInstance()->getFatPlayer($this->m_player)->getTeam();
                                    $bedLoc = Bedwars::getInstance()->getBedwarsConfig()->getBedLocation($team);
                                    $this->m_player->setGamemode(3);
                                    $this->m_player->addTitle("", "\n\n§41 sec cooldown...");
                                    $this->m_player->teleport($bedLoc);

                                    LoadBalancer::getInstance()->getServer()->getScheduler()->scheduleDelayedTask(new class(Bedwars::getInstance(), $this->m_player, $bedLoc) extends PluginTask
                                    {
                                        private $m_player;
                                        private $m_loc;

                                        public function __construct(PluginBase $p_Plugin, Player $p_player, Vector3 $p_loc)
                                        {
                                            parent::__construct($p_Plugin);
                                            $this->m_player = $p_player;
                                            $this->m_loc = $p_loc;
                                        }

                                        public function onRun(int $currentTick)
                                        {
                                            $this->m_player->setGamemode(0);
                                            $this->m_player->setHealth($this->m_player->getMaxHealth());
                                            $this->m_player->teleport($this->m_loc);
                                        }
                                    }, 40);

                                }
                            }, 5);
                            return;
                        }
                        $entity->kill();
                    }
                }
            }
        }

    }

    public function onPlayerQuit(PlayerQuitEvent $p_Event)
    {
        if (GameManager::getInstance()->isPlaying()) {
            $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Event->getPlayer());
            if ($l_FatPlayer != null)
                $l_FatPlayer->setOutOfGame();

            $this->checkGameState();
        }

        Sidebar::getInstance()->update();

        new DelayedExec(function () use ($p_Event) {
            if (GameManager::getInstance()->isWaiting()) {
                $l_Team = TeamsManager::getInstance()->getPlayerTeam($p_Event->getPlayer());
                if ($l_Team instanceof Team)
                    $l_Team->removePlayer($p_Event->getPlayer());

                if ($this->m_WaitingTimer instanceof Timer && $this->m_WaitingTimer->getTickLeft() > 0 &&
                    (count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer())) {
                    $this->m_WaitingTimer->cancel();
                    $this->m_WaitingTimer = null;
                    $this->resetGameWaiting();
                }
            }/* else if (GameManager::getInstance()->isPlaying() && count($this->getServer()->getOnlinePlayers()) == 0) {
				$this->getServer()->shutdown();
			}*/
        }, 1);
    }

    private function resetGameWaiting()
    {
        // Waiting Clock Initialization
        $this->m_WaitingTimer = new DisplayableTimer(GameManager::getInstance()->getWaitingTickDuration());
        $this->m_WaitingTimer
            ->setTitle(new TextFormatter("timer.waiting.title"))
            ->addStopCallback(function () {
                $this->startGame();
            })
            ->addSecondCallback(function () {
                if ($this->m_WaitingTimer instanceof Timer) {
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

        $line = "template.bw";
        if ($this->getBedwarsConfig()->isFastRush())
            $line = "template.fastRush";
        Sidebar::getInstance()->clearLines();
        // Waiting Sidebar Initialization
        Sidebar::getInstance()
            ->addTranslatedLine(new TextFormatter($line))
            ->addTranslatedLine(new TextFormatter("template.playfatcraft"))
            ->addTimer($this->m_WaitingTimer)
            ->addWhiteSpace()
            ->addMutableLine(function () {
                return new TextFormatter("game.waitingForMore", ["amount" => max(0, PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers()))]);
            });
        Sidebar::getInstance()->update();
    }

    public function dropPlayerMoney(Player $p_Player)
    {
        if ($p_Player == null || PlayersManager::getInstance()->getFatPlayer($p_Player))
            return;
        if ($this->getPlayerIron($p_Player) > 0) {
            $p_Player->getLevel()->dropItem($p_Player, Item::get(ItemIds::IRON_INGOT, 0, $this->getPlayerIron($p_Player)));
            $this->setPlayerIron($p_Player, 0);
        }
        if ($this->getPlayerGold($p_Player) > 0) {
            $p_Player->getLevel()->dropItem($p_Player, Item::get(ItemIds::GOLD_INGOT, 0, $this->getPlayerGold($p_Player)));
            $this->setPlayerGold($p_Player, 0);
        }
        if ($this->getPlayerDiamond($p_Player) > 0) {
            $p_Player->getLevel()->dropItem($p_Player, Item::get(ItemIds::DIAMOND, 0, $this->getPlayerDiamond($p_Player)));
            $this->setPlayerDiamond($p_Player, 0);
        }
    }

    private function removePlayerFromGame(Player $p_player, String $p_deathMessage = "")
    {
        PlayersManager::getInstance()->getFatPlayer($p_player)->setOutOfGame();
        $team = PlayersManager::getInstance()->getFatPlayer($p_player)->getTeam();

        if ($team->getInGamePlayerLeft() == 0)
        {
            foreach ($team->getPlayersUuid() as $p_PlayerUuid)
            ScoresManager::getInstance()->giveRewardToPlayer($p_PlayerUuid, ((GameManager::getInstance()->getPlayerNbrAtStart() - PlayersManager::getInstance()->getInGamePlayerLeft()) / GameManager::getInstance()->getPlayerNbrAtStart()));
        }

        WorldUtils::addStrike($p_player->getLocation());

        foreach (Bedwars::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $l_Player->sendMessage($team->getPrefix() . " " . $p_deathMessage);

        $this->checkGameState();

        $p_player->setGamemode(3);

        Sidebar::getInstance()->update();
    }


     /**
     * @param PlayerDeathEvent $e
     */
    public function playerDeathEvent(PlayerDeathEvent $e)
    {
        $p = $e->getEntity();
        $team = PlayersManager::getInstance()->getFatPlayer($p)->getTeam();
        $bedLoc = $this->getBedwarsConfig()->getBedLocation($team);

        $e->setKeepInventory(false);

        // Remove player items

        $this->dropPlayerMoney($p);

        if ($bedLoc->getLevel()->getBlockIdAt($bedLoc->getFloorX(), $bedLoc->getFloorY(), $bedLoc->getFloorZ()) == $this->bedBlockId)
            return;

        $this->removePlayerFromGame($p, $e->getDeathMessage());

/*        PlayersManager::getInstance()->getFatPlayer($p)->setOutOfGame();

        if ($team->getInGamePlayerLeft() == 0)
		{
			foreach ($team->getPlayersUuid() as $p_PlayerUuid)
				ScoresManager::getInstance()->giveRewardToPlayer($p_PlayerUuid, ((GameManager::getInstance()->getPlayerNbrAtStart() - PlayersManager::getInstance()->getInGamePlayerLeft()) / GameManager::getInstance()->getPlayerNbrAtStart()));
		}

        WorldUtils::addStrike($p->getLocation());

        foreach (Bedwars::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $l_Player->sendMessage($team->getPrefix() . " " . $e->getDeathMessage());

        $this->checkGameState();

        $e->setDeathMessage("");
        $p->setGamemode(3);

        Sidebar::getInstance()->update();*/
    }

    public function checkGameState(): void
    {
        $l_TeamLeft = TeamsManager::getInstance()->getInGameTeamNbr();
        if ($l_TeamLeft <= 1)
        {
            if (Bedwars::DEBUG)
                echo "Should be a end game but cancelled cause debug is on\n";
            else
                Bedwars::getInstance()->endGame();
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        $player = null;
        if ($sender instanceof Player)
        {
            $player = $sender;
        } else
        {
            echo "sender is not a player\n";
        }

        if (Bedwars::DEBUG)
        {
            $firstSwitch = true;
            switch ($args[0])
            {
                case "team":
                {
                    TeamsManager::getInstance()->displayTeamSelection($player);
                }
                    break;
                case"balance":
                {
                    TeamsManager::getInstance()->balanceTeams();
                }
                    break;
                case "shop":
                {
                    ShopKeeper::openShop($player);
                }
                    break;
                default:
                    $firstSwitch = false;
            }
            if ($firstSwitch)
                return true;
        }


        if (!$sender->isOp())
        {
            $sender->sendMessage("you need to be op");
            return false;
        }

        switch ($args[0])
        {
            case "upgrade":
            {
                $team = PlayersManager::getInstance()->getFatPlayer($player)->getTeam();
                $this->upgradeIronForge($team);
                echo "Forge " . $team->getColoredName() . " upgraded\n";
            }
                break;
            case "gold":
            {
                $this->upgradeGoldForges();
            }
                break;
            case "diam":
            {
                $this->upgradeDiamondForges();
            }
                break;
            case "team":
            {
                TeamsManager::getInstance()->displayTeamSelection($player);
            }
                break;
            case"balance":
            {
                TeamsManager::getInstance()->balanceTeams();
            }
                break;
            case "npc":
            {
                TeamsManager::getInstance()->addNPC($player->getLocation());
            }
                break;
            case "clear":
            {
                TeamsManager::getInstance()->clearNPCs();
            }
                break;
        }
        return true;
    }

    public function getBedBlockId()
    {
        return $this->bedBlockId;
    }
}
