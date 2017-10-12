<?php
/**
 * Created by Unikaz.
 */

namespace fatcraft\murder;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\scores\PlayerScoresManager;
use fatutils\scores\ScoresManager;
use fatutils\scores\TeamScoresManager;
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
use fatutils\tools\BossbarTimer;
use fatutils\ui\WindowsManager;
use MSpawns\Commands\Spawn;
use pocketmine\block\BlockIds;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
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
    private $m_MurderConfig;
    private static $m_Instance;
    private $m_WaitingTimer;
    private $m_PlayTimer;

    private $m_murdererUUID;


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
        echo "### MURDER ###\n";
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getCommand("murder")->setExecutor(self::$m_Instance);

        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        $this->m_MurderConfig = new MurderConfig($this->getConfig());
        $this->initialize();
    }

    private function initialize()
    {
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_OPEN);
        PlayersManager::getInstance()->displayHealth();
        WorldUtils::stopWorldsTime();

        Sidebar::getInstance()
            ->addTranslatedLine(new TextFormatter("murder.sidebar.title"))
            ->addWhiteSpace()
            ->addMutableLine(function (Player $p_Player) {
                return [
                    1, 2, 3
                ];
            });
    }

    public function handlePlayerConnection(PlayerJoinEvent $p_event)
    {
        $p_Player = $p_event->getPlayer();
        $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

        if (GameManager::getInstance()->isWaiting()) {

            $p_Player->setGamemode(Player::ADVENTURE);

            if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMaxPlayer()) {
                $this->getLogger()->info("MAX PLAYER REACH !");
                if ($this->m_WaitingTimer instanceof Timer)
                    $this->m_WaitingTimer->cancel();
                $this->startGame();
            } else if (count($this->getServer()->getOnlinePlayers()) >= PlayersManager::getInstance()->getMinPlayer()) {
                if (is_null($this->m_WaitingTimer)) {
                    $this->getLogger()->info("MIN PLAYER REACH !");
                    $this->m_WaitingTimer = (new BossbarTimer(GameManager::getInstance()->getWaitingTickDuration()))
                        ->setTitle("Debut dans")
                        ->addStopCallback(function () {
                            $this->startGame();
                        })
                        ->start();
                }
            } else if (count($this->getServer()->getOnlinePlayers()) < PlayersManager::getInstance()->getMinPlayer()) {
                $l_WaitingFor = PlayersManager::getInstance()->getMinPlayer() - count($this->getServer()->getOnlinePlayers());
                foreach ($this->getServer()->getOnlinePlayers() as $l_Player)
                    $l_Player->sendTip((new TextFormatter("game.waitingForMore", ["amount" => $l_WaitingFor]))->asStringForPlayer($l_Player));
            }
        }

        Sidebar::getInstance()->update();
    }


    //---------------------
    // UTILS
    //---------------------
    public function startGame()
    {
        LoadBalancer::getInstance()->setServerState(LoadBalancer::SERVER_STATE_CLOSED);

        // Clear windows registry to avoid having player choosing team after start
        WindowsManager::getInstance()->clearRegistry();

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            SpawnManager::getInstance()->getRandomEmptySpawn()->teleport($player);
        }

        $this->m_PlayTimer = (new BossbarTimer(GameManager::getInstance()->getPlayingTickDuration()))
            ->setTitle(new TextFormatter("bossbar.playing.title"))
            ->addStartCallback(function () {
                FatUtils::getInstance()->getLogger()->info("Game end timer starts !");

                $l_GoMsgFormatter = new TextFormatter("game.start");
                foreach ($this->getServer()->getOnlinePlayers() as $l_Player) {
                    $l_Team = TeamsManager::getInstance()->getPlayerTeam($l_Player);

                    if ($l_Team instanceof Team) {
                        PlayersManager::getInstance()->getFatPlayer($l_Player)->setPlaying();
                        $l_Player->setGamemode(Player::SURVIVAL);
                        $l_Team->getSpawn()->teleport($l_Player, 2);
                        $l_Player->addTitle($l_GoMsgFormatter->asStringForPlayer($l_Player));
                    }
                }

                Sidebar::getInstance()->update();
            })
            ->addTickCallback([$this, "onPlayingTick"])
            ->addStopCallback(function () {
                $this->endGame();
            })
            ->start();

        GameManager::getInstance()->startGame();
        SpawnManager::getInstance()->unblockSpawns();
    }

    public function onPlayingTick()
    {
    }

    public function endGameMurderer()
    {
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
            $l_Player->addTitle(
                (new TextFormatter("murder.murderWin"))->asStringForPlayer($l_Player),
                (new TextFormatter("murder.murderWin.named"))->addParam("name", PlayersManager::getInstance()->getFatPlayerByUUID($this->m_murdererUUID)->getName())->asStringForPlayer($l_Player),
                30, 80, 30);
        }
        //reward murderer
        $this->giveReward(PlayersManager::getInstance()->getFatPlayerByUUID($this->m_murdererUUID), 100, 10);
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {

            //todo restart here !
            $this->giveReward(PlayersManager::getInstance()->getFatPlayerByUUID($this->m_murdererUUID), 100, 10);
        }


        $this->endGame();
    }

    public function endGameLambdas(player $killer)
    {
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
            $l_Player->addTitle(
                (new TextFormatter("murder.lambdasWin"))->asStringForPlayer($l_Player),
                (new TextFormatter("murder.lambdasWin.named"))->addParam("name", $killer->getName())->asStringForPlayer($l_Player),
                30, 80, 30);
        }

        $this->endGame();
    }

    public function endGame()
    {
        if ($this->m_PlayTimer instanceof Timer)
            $this->m_PlayTimer->cancel();


        TeamScoresManager::getInstance()->giveRewards();

        (new BossbarTimer(150))
            ->setTitle(new TextFormatter("bossbar.returnToLobby"))
            ->addStopCallback(function () {
                foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player) {
                    LoadBalancer::getInstance()->balancePlayer($l_Player, "lobby");
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

    public function giveReward(FatPlayer $p_fatPlayer, int $p_money, int $p_xp)
    {
        $l_Player = $p_fatPlayer->getPlayer();
        // add general stats
        \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("Money", $l_Player, $p_money);
        \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry("XP", $l_Player, $p_xp);

        $l_Player->sendMessage((new TextFormatter("reward.endGame.money", ["amount" => $p_money]))->asStringForFatPlayer($p_fatPlayer));
        $l_Player->sendMessage((new TextFormatter("reward.endGame.xp", ["amount" => $p_xp]))->asStringForFatPlayer($p_fatPlayer));

        // add game specific stats
        $l_ServerType = \fatcraft\loadbalancer\LoadBalancer::getInstance()->getServerType();
        \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_XP", $l_Player, $p_xp);
        \SalmonDE\StatsPE\CustomEntries::getInstance()->modIntEntry($l_ServerType . "_played", $l_Player, 1);

        $datas = ["xp"=>$p_xp, "money"=>$p_money];
        if($l_Player->getUniqueId()->equals($this->m_murdererUUID))
            $datas["isMurderer"] = true;
        PlayerScoresManager::getInstance()->recordScore($l_Player->getUniqueId(), 0, $datas);
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
                if ($item->getId() == ItemIds::IRON_SWORD || ($item->getId() == ItemIds::BOW && $p_event instanceof EntityDamageByChildEntityEvent)) {
                    // someone was killed by a gunner
                    $p_event->setDamage(2000);
                    return;
                }
            }
        }
        $p_event->setCancelled(true);

    }

    public function onPlayerQuit(PlayerQuitEvent $p_Event)
    {
        new DelayedExec(1, function () {
            if (GameManager::getInstance()->isPlaying()) {
                Sidebar::getInstance()->update();
            }
        });
    }

    /**
     * @param PlayerDeathEvent $e
     */
    public function playerDeathEvent(PlayerDeathEvent $e)
    {
        //todo test if it's the murderer or a player

        $p = $e->getEntity();
        PlayersManager::getInstance()->getFatPlayer($p)->setHasLost();

        $customDeathMessage = "";

        //if it's the murderer
        if ($p->getUniqueId()->equals($this->m_murdererUUID)) {
            $killer = null;
            $lastDamageEvent = $p->getLastDamageCause();
            if ($lastDamageEvent instanceof EntityDamageByEntityEvent)
                $killer = $lastDamageEvent->getDamager();
            if ($killer instanceof Player)
                $customDeathMessage = $p->getName() . " était le meurtrier et a été tué par " . $killer->getName();
            else
                $customDeathMessage = $p->getName() . " était le meurtrier et est mort";

            // endGame, lambdas win
            $this->endGameLambdas($killer);

        } else {
            $customDeathMessage = $p->getName() . " a été tué";
            if (PlayersManager::getInstance()->getAlivePlayerLeft() <= 1) {
                // endgame, murderer wins
                $this->endGameMurderer();
            } else {
                // else heu... the game continue ^^
            }
        }
        $e->setDeathMessage($customDeathMessage);

        $p->setGamemode(3);
        Sidebar::getInstance()->update();
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
