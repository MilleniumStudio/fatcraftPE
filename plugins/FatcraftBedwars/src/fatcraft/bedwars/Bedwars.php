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
use fatutils\tools\Timer;
use fatutils\tools\WorldUtils;
use fatutils\game\GameManager;
use fatutils\spawns\SpawnManager;
use fatutils\tools\MathUtils;
use fatutils\tools\BossbarTimer;
use pocketmine\block\BlockIds;
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

class Bedwars extends PluginBase implements Listener
{
    const CONFIG_KEY_FORGES_ROOT = "forges";
    const CONFIG_KEY_FORGES_LOCATION = "location";
    const CONFIG_KEY_FORGES_ITEM_TYPE = "itemType";
    const CONFIG_KEY_FORGES_POP_DELAY = "popDelay";
    const CONFIG_KEY_FORGES_POP_AMOUNT = "popAmount";

    const PLAYER_DATA_CURRENCY_IRON = "currency.iron";
    const PLAYER_DATA_CURRENCY_GOLD = "currency.gold";
    const PLAYER_DATA_CURRENCY_DIAMOND = "currency.diamond";

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
        if ($this->getConfig()->exists(TeamsManager::CONFIG_KEY_TEAM_ROOT))
        {
            FatUtils::getInstance()->getLogger()->info("FORGES loading...");
            foreach ($this->getConfig()->get(Bedwars::CONFIG_KEY_FORGES_ROOT) as $key => $value)
            {
                if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_LOCATION, $value))
                {
                    $newForge = new Forge(WorldUtils::stringToLocation($value[Bedwars::CONFIG_KEY_FORGES_LOCATION]));

                    if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_ITEM_TYPE, $value))
                        $newForge->setItemType(ItemUtils::getItemIdFromName($value[Bedwars::CONFIG_KEY_FORGES_ITEM_TYPE]));

                    if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_POP_DELAY, $value))
                        $newForge->setPopDelay($value[Bedwars::CONFIG_KEY_FORGES_POP_DELAY]);

                    if (array_key_exists(Bedwars::CONFIG_KEY_FORGES_POP_AMOUNT, $value))
                        $newForge->setPopAmount($value[Bedwars::CONFIG_KEY_FORGES_POP_AMOUNT]);

                    FatUtils::getInstance()->getLogger()->info("   - " . $key);
                    $this->m_Forges[] = $newForge;
                }
            }
        }

/*        Sidebar::getInstance()
            ->setUpdateTickInterval(40)
            ->addLine(TextFormat::DARK_GREEN . TextFormat::BOLD . "== Bedwars ==")
            ->addWhiteSpace()
            ->addLine(TextFormat::DARK_PURPLE . TextFormat::BOLD . "< TEAMS >")
            ->addMutableLine(function ()
            {
                $l_Ret = [];

                foreach (TeamsManager::getInstance()->getTeams() as $l_Team)
                {
                    if ($l_Team instanceof Team)
                    {
                        $l_State = "";
                        if ($l_Team->getSpawn()->isActive())
                            $l_State = TextFormat::GREEN . "OK";
                        else
                        {
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
            ->addLine(TextFormat::DARK_PURPLE . TextFormat::BOLD . "< MONNAIES >")
            ->addMutableLine(function (Player $p_Player)
            {
                $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

                return [
                    TextFormat::GRAY . "IRON" . TextFormat::WHITE . " : " . TextFormat::GRAY . $this->getPlayerIron($p_Player),
                    TextFormat::GOLD . "GOLD" . TextFormat::WHITE . " : " . TextFormat::GOLD . $this->getPlayerGold($p_Player),
                    TextFormat::AQUA . "DIAMOND" . TextFormat::WHITE . " : " . TextFormat::AQUA . $this->getPlayerDiamond($p_Player)
                ];
            });*/
    }

    public function handlePlayerConnection(Player $p_Player)
    {
        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

        $l_Spawn = SpawnManager::getInstance()->getRandomEmptySpawn();
        if (GameManager::getInstance()->isWaiting() && !is_null($l_Spawn))
        {
            FatUtils::getInstance()->getLogger()->info("WELCOME TO " . $p_Player->getName());
            $l_Team = TeamsManager::getInstance()->addInBestTeam($p_Player);
            $l_Spawn->teleport($p_Player, 3);
            $p_Player->setGamemode(Player::SURVIVAL);

            new DelayedExec(5, function() use ($p_Player, $l_Team)
            {
                $p_Player->addSubTitle("Vous êtes dans la team " . $l_Team->getName());
            });

            (new Timer(600))
                ->addStartCallback(function() {
                    FatUtils::getInstance()->getLogger()->info("Forges are starting up !");
                })
                ->addTickCallback(function () use ($l_FatPlayer)
                {
                    if ($this->getServer()->getTick() % 20 == 0)
                    {
                        foreach ($this->m_Forges as $l_Forge)
                        {
                            if ($l_Forge instanceof Forge)
                            {
                                if ($l_Forge->canPop())
                                    $l_Forge->pop();
                            }
                        }
                    }
                })
                ->start();
        } else
        {
            $p_Player->setGamemode(3);
            $p_Player->sendMessage(TextFormat::YELLOW . "You've been automatically set to SPECTATOR");
            $this->getServer()->getLogger()->info($p_Player->getName() . " has been set to SPECTATOR");
        }

        Sidebar::getInstance()->update();
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
                    $event->getPlayer()->getInventory()->addItem(ItemFactory::get(Item::GOLD_BLOCK,0, 10));
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

        foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
        {
            PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
            $l_Player->addTitle(TextFormat::GREEN . "GO !");
        }

        $this->m_PlayTimer = (new BossbarTimer(GameManager::getInstance()->getPlayingTickDuration()))
            ->setTitle("Fin de la partie dans")
            ->addStopCallback(function ()
            {
                if (PlayersManager::getInstance()->getAlivePlayerLeft() <= 1)
                    $this->endGame();
                else
                {
                    $l_ArenaLoc = Location::fromObject($this->getBedwarsConfig()->getDeathArenaLoc());

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
        if (count($winners) > 0) {
            $winner = $winners[0];
            if ($winner instanceof FatPlayer)
                $winnerName = $winner->getPlayer()->getName();
        }
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $l_Player->addTitle(TextFormat::DARK_AQUA . TextFormat::BOLD . "Partie terminée", TextFormat::GREEN . TextFormat::BOLD . "le vainqueur est " . $winnerName, 30, 80, 30);

        (new BossbarTimer(150))
            ->setTitle("Retour au lobby")
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
}
