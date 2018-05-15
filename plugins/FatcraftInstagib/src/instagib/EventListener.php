<?php

namespace instagib;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\game\GameManager;
use fatutils\players\PlayersManager;
use fatutils\scores\ScoresManager;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\tools\Sidebar;
use fatutils\tools\WorldUtils;
use fatutils\spawns\SpawnManager;
use battleroyal\BattleRoyal;
use libasynql\result\MysqlResult;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\EntityIds;
use pocketmine\entity\projectile\Grenada;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\lang\TextContainer;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{

    public function __construct()
    {
    }

    /**
     * @param PlayerJoinEvent $e
     */
    public function onPlayerJoin(PlayerJoinEvent $e)
    {
        $l_Player = $e->getPlayer();
        echo ("YO !! player join event\n");
        if (GameManager::getInstance()->isPlaying() || count(LoadBalancer::getInstance()->getServer()->getOnlinePlayers()) > PlayersManager::getInstance()->getMaxPlayer())
        {
            if ($l_Player->isOp()) {
                $l_Player->setGamemode(3);
                PlayersManager::getInstance()->getFatPlayer($l_Player)->setOutOfGame();
                return;
            }
            else
            {
                LoadBalancer::getInstance()->balancePlayer($l_Player, LoadBalancer::TEMPLATE_TYPE_LOBBY);
                return;
            }
        }
        Instagib::getInstance()->handlePlayerLogin($l_Player);
    }

    public function onBlocBreakEvent(BlockBreakEvent $e)
    {
        $e->setCancelled(true);
    }

    public function onPlayerRespawn(PlayerRespawnEvent $p_Event)
    {

    }

    public function onChunkUnload(\pocketmine\event\level\ChunkUnloadEvent $p_event)
    {
        $p_event->setCancelled();
    }

    public function onPlayerExhaust(PlayerExhaustEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    private function playerJustDie(Player $p_player)
    {
        Instagib::getInstance()->getServer()->getScheduler()->scheduleDelayedTask(new class(Instagib::getInstance(), $p_player) extends PluginTask
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
                $deadSpawnLoc = $this->m_player->getSpawn();
                $deadSpawnLoc->y += 30;
                $this->m_player->setGamemode(3);
                $this->m_player->addTitle("", "\n\n§43 sec cooldown...");
                $this->m_player->teleport($deadSpawnLoc);

                $nextSpawnLoc = SpawnManager::getInstance()->getRandomEmptySpawn();

                LoadBalancer::getInstance()->getServer()->getScheduler()->scheduleDelayedTask(new class(Instagib::getInstance(), $this->m_player, $nextSpawnLoc->getLocation()) extends PluginTask
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

    }

    public function onPlayerDamage(EntityDamageEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
        {
            $e->setCancelled(true);
            if ($e->getCause() == EntityDamageEvent::CAUSE_FALL)
                return;
            if ($e->getCause() == EntityDamageEvent::CAUSE_VOID)
            {
                if (Instagib::getInstance()->isGameStarted())
                    Instagib::getInstance()->scoreForPlayer($p, -1);
                $this->playerJustDie($p);
                return;
            }
            else
            {
                if (!Instagib::getInstance()->isGameStarted())
                    return;
                    $l_player = null;
                if($e instanceof EntityDamageByEntityEvent)
                {
                    $l_player = $e->getDamager();

                    // hand damage case
                    if ($l_player instanceof Player)
                    {
                        Instagib::getInstance()->scoreForPlayer($l_player, 1);
                    }

                    // bullet damage case
                    else
                    {
                        $l_player = $e->getEntity()->getOwningEntity();

                        if ($l_player instanceof Player)
                        {
                            Instagib::getInstance()->scoreForPlayer($l_player, 1);
                        }
                    }
                }
                $this->playerJustDie($p);
            }
        }
    }
}
