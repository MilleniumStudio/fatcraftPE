<?php

namespace fatcraft\bedwars;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\loot\ChestsManager;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use fatutils\tools\bossBarAPI\BossBarAPI;
use fatutils\tools\DelayedExec;
use fatutils\tools\ItemUtils;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\Timer;
use fatutils\tools\WorldUtils;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use fatutils\tools\MathUtils;
use fatutils\tools\BossbarTimer;
use pocketmine\block\BlockIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;

class Bedwars extends PluginBase implements Listener
{
    const DEBUG = true;
    const CONFIG_KEY_FORGES_ROOT = "forges";
    const CONFIG_KEY_FORGES_LOCATION = "location";
    const CONFIG_KEY_FORGES_ITEM_TYPE = "itemType";
    const CONFIG_KEY_FORGES_POP_DELAY = "popDelay";
    const CONFIG_KEY_FORGES_POP_AMOUNT = "popAmount";

    const PLAYER_DATA_CURRENCY_IRON = "currency.iron";
    const PLAYER_DATA_CURRENCY_GOLD = "currency.gold";
    const PLAYER_DATA_CURRENCY_DIAMOND = "currency.diamond";

    const BLOCK_ID = BlockIds::BEACON;

    private $m_BedwarsConfig;
    private static $m_Instance;
    private $m_WaitingTimer;
    private $m_PlayTimer;

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
        //        SpawnManager::getInstance()->blockSpawns();
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);
        PlayersManager::getInstance()->displayHealth();
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
                            $newForge->setPopDelay($index, $delay);
                        }
//                        $newForge->setPopDelay($value[Bedwars::CONFIG_KEY_FORGES_POP_DELAY]);
                    }


                    if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_POP_AMOUNT, $value))
                        $newForge->setPopAmount($value[Bedwars::CONFIG_KEY_FORGES_POP_AMOUNT]);

                    FatUtils::getInstance()->getLogger()->info("   - " . $key);
                    $this->m_Forges[] = $newForge;
                }
            }
        }

        Sidebar::getInstance()
//            ->setUpdateTickInterval(40)
            ->addTranslatedLine(new TextFormatter("bedwars.sidebar.title"))
            ->addWhiteSpace()
            ->addTranslatedLine(new TextFormatter("bedwars.sidebar.teams.title"))
            ->addMutableLine(function () {
                $l_Ret = [];

                foreach (TeamsManager::getInstance()->getTeams() as $l_Team) {
                    if ($l_Team instanceof Team) {
                        $l_State = "";
                        $bedLocation = $this->getBedwarsConfig()->getBedLocation($l_Team);
                        if (Server::getInstance()->getDefaultLevel()->getBlockIdAt($bedLocation->getFloorX(), $bedLocation->getFloorY(), $bedLocation->getFloorZ()) == self::BLOCK_ID)
                            $l_State = TextFormat::GREEN . "OK";
                        else {
                            $l_AliveTeamPlayer = $l_Team->getAlivePlayerLeft();
                            if ($l_AliveTeamPlayer > 0)
                                $l_State = TextFormat::AQUA . $l_AliveTeamPlayer;
                            else
                                $l_State = TextFormat::RED . "X";
                        }

                        $l_Ret[] = $l_Team->getName() . TextFormat::WHITE . " : " . $l_State;
                    }
                }

                return $l_Ret;
            })
            ->addWhiteSpace()
            ->addTranslatedLine(new TextFormatter("bedwars.sidebar.currencies.title"))
            ->addMutableLine(function (Player $p_Player) {
                return [
                    new TextFormatter("bedwars.sidebar.currency.iron", ["amount" => $this->getPlayerIron($p_Player)]),
                    new TextFormatter("bedwars.sidebar.currency.gold", ["amount" => $this->getPlayerGold($p_Player)]),
                    new TextFormatter("bedwars.sidebar.currency.diamond", ["amount" => $this->getPlayerDiamond($p_Player)])
                ];
            });
    }

    public function handlePlayerConnection(Player $p_Player)
    {
        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

        $l_Team = TeamsManager::getInstance()->addInBestTeam($p_Player);
        if (GameManager::getInstance()->isWaiting() && !is_null($l_Team) && !is_null($l_Team->getSpawn())) {
            $l_Team->getSpawn()->teleport($p_Player, 3);
            $p_Player->setGamemode(Player::ADVENTURE);

            new DelayedExec(5, function () use ($p_Player, $l_Team) {
                $p_Player->addSubTitle((new TextFormatter("player.team.join", ["teamName", $l_Team->getName()]))->asStringForPlayer($p_Player));
            });
        } else {
            $p_Player->setGamemode(3);
            $p_Player->sendMessage((new TextFormatter("player.autoSwitchToSpec"))->asStringForFatPlayer($l_FatPlayer));
            $this->getServer()->getLogger()->info($p_Player->getName() . " has been automatically set to SPECTATOR");
        }

        Sidebar::getInstance()->update();

        if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer()) {
            $this->startGame();
        }
    }

    //---------------------
    // CURRENCIES
    //---------------------
    public function modPlayerIron(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->addData(Bedwars::PLAYER_DATA_CURRENCY_IRON, $p_Value);
    }

    public function modPlayerGold(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->addData(Bedwars::PLAYER_DATA_CURRENCY_GOLD, $p_Value);
    }

    public function modPlayerDiamond(Player $p_Player, int $p_Value)
    {
        PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->addData(Bedwars::PLAYER_DATA_CURRENCY_DIAMOND, $p_Value);
    }

    public function getPlayerIron(Player $p_Player)
    {
        return PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->getData(Bedwars::PLAYER_DATA_CURRENCY_IRON, 0);
    }

    public function getPlayerGold(Player $p_Player)
    {
        return PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->getData(Bedwars::PLAYER_DATA_CURRENCY_GOLD, 0);
    }

    public function getPlayerDiamond(Player $p_Player)
    {
        return PlayersManager::getInstance()->getFatPlayer($p_Player)
            ->getData(Bedwars::PLAYER_DATA_CURRENCY_DIAMOND, 0);
    }

    public function upgradeIronForge(Team $p_team): Forge
    {
        /** @var var Forge $forge */
        foreach ($this->m_Forges as $forge) {
            if ($forge->getTeam() != null && $forge->getTeam() == $p_team) {
                $forge->upgrade();
                return $forge;
            }
        }
        return null;
    }


    //-------------------------------
    // TEST for SHOP via INVENTORIES
    //-------------------------------
    public $i = 0;
    public $lastPacket = [];

    /**
     * @priority LOWEST
     */
    public function onPacket(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        if ($packet instanceof ContainerSetSlotPacket) {
            echo $this->i++;
            if (!$packet->item instanceof Air) {
                echo "#";
                if ($packet->item->getNamedTagEntry("shop") != "") {
                    $event->setCancelled(true);
                    $event->getPlayer()->getInventory()->addItem(ItemFactory::get(Item::GOLD_BLOCK, 0, 10));
                    $event->getPlayer()->getInventory()->resetHotbar(true);
                }
            }
            echo "\n";
        }
    }


    //---------------------
    // UTILS
    //---------------------
    public function startGame()
    {
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);

        foreach ($this->getServer()->getOnlinePlayers() as $l_Player) {
            PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
            $l_Player->setGamemode(Player::SURVIVAL);
            $l_Player->addTitle(TextFormat::GREEN . "GO !");
        }

        $this->m_PlayTimer = (new BossbarTimer(GameManager::getInstance()->getPlayingTickDuration()))
            ->setTitle(new TextFormatter("bossbar.playing.title"))
            ->addStartCallback(function () {
                FatUtils::getInstance()->getLogger()->info("Game end timer starts !");
                FatUtils::getInstance()->getLogger()->info("Forges are heating up !");
            })
            ->addTickCallback(function () {
                if ($this->getServer()->getTick() % 20 == 0) {
                    foreach ($this->m_Forges as $l_Forge) {
                        if ($l_Forge instanceof Forge) {
                            if ($l_Forge->canPop())
                                $l_Forge->pop();
                        }
                    }
                }
            })
            ->addStopCallback(function () {
                if (PlayersManager::getInstance()->getAlivePlayerLeft() <= 1)
                    $this->endGame();
                else {
                    $l_ArenaLoc = Location::fromObject($this->getBedwarsConfig()->getDeathArenaLoc());

                    foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
                        $l_Player->addSubTitle(TextFormat::DARK_AQUA . TextFormat::BOLD . "Timer terminé, match à mort dans l'arène !");
                        $l_Player->teleport(WorldUtils::getRandomizedLocation($l_ArenaLoc, 3, 0, 3));
                        $l_Player->sendTip(TextFormat::YELLOW . "Vous êtes invulnérable pendant 5 secondes" . TextFormat::RESET);
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
        if (count($winners) > 0) {
            $winner = $winners[0];
            if ($winner instanceof FatPlayer)
                $winnerName = $winner->getPlayer()->getName();
        }
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $l_Player->addTitle(
                (new TextFormatter("game.end"))->asStringForPlayer($l_Player),
                (new TextFormatter("game.winner.single"))->addParam("name", $winnerName)->asStringForPlayer($l_Player),
                30, 80, 30);

        (new BossbarTimer(150))
            ->setTitle(new TextFormatter("bossbar.returnToLobby"))
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
    public function getBedwarsConfig(): BedwarsConfig
    {
        return $this->m_BedwarsConfig;
    }

    //---------------------
    // Event
    //---------------------
    /**
     * @param PlayerDeathEvent $e
     */
    public function playerDeathEvent(PlayerDeathEvent $e)
    {
        $p = $e->getEntity();
        $team = PlayersManager::getInstance()->getFatPlayer($p)->getTeam();

        $bedLoc = $this->getBedwarsConfig()->getBedLocation($team);
        if ($bedLoc->getLevel()->getBlockIdAt($bedLoc->getFloorX(), $bedLoc->getFloorY(), $bedLoc->getFloorZ()) == self::BLOCK_ID) {
            //bed is still here
            return;
        }

        PlayersManager::getInstance()->getFatPlayer($p)->setHasLost(true);

        WorldUtils::addStrike($p->getLocation());
        $l_PlayerLeft = PlayersManager::getInstance()->getAlivePlayerLeft();


        foreach (Bedwars::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
            $l_Player->sendMessage($e->getDeathMessage());
            if ($l_PlayerLeft > 1)
                $l_Player->sendMessage("Il reste " . TextFormat::YELLOW . PlayersManager::getInstance()->getAlivePlayerLeft() . TextFormat::RESET . " survivants !", "*");
        }

        if ($l_PlayerLeft <= 1 && !Bedwars::DEBUG)
            Bedwars::getInstance()->endGame();

        $e->setDeathMessage("");
        $p->setGamemode(3);

        Sidebar::getInstance()->update();
    }


    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        if (!$sender->isOp()) {
            $sender->sendMessage("you need to be op");
            return false;
        }
        $player = null;
        if ($sender instanceof Player) {
            $player = $sender;
        } else {
            echo "sender is not a player\n";
        }
        switch ($args[0]) {
            case "upgrade": {
                $team = PlayersManager::getInstance()->getFatPlayer($player)->getTeam();
                $forge = $this->upgradeIronForge($team);
                echo "Forge " . $team->getName() . " upgrade to level " . $forge->getLevel()."\n";
           }break;
        }
        $sender->sendMessage("something");
        return true;
    }
}
