<?php

namespace fatutils;

use fatutils\ban\BanManager;
use fatutils\game\GameManager;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\shop\ShopItem;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\tools\TextFormatter;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\event\player\PlayerDeathEvent;
use fatutils\gamedata\GameDataManager;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\block\BlockIds;
use pocketmine\level\Position;
use pocketmine\level\particle\Particle;
use fatutils\tools\particles\ParticleBuilder;
use pocketmine\event\player\PlayerItemHeldEvent;

class EventListener implements Listener
{
    public function onLogin(PlayerLoginEvent $e)
    {
        if (BanManager::getInstance()->isBanned($e->getPlayer()))
        {
            $l_ExpirationTimestamp = BanManager::getInstance()->getPlayerBan($e->getPlayer())->getExpirationTimestamp();
            if (!is_null($l_ExpirationTimestamp))
                $e->setKickMessage("You're banned from this server until " . date("D M j G:i:s Y", $l_ExpirationTimestamp) . ".");
            else
                $e->setKickMessage("You're definitely banned from this server.");
            $e->setCancelled(true);
        }

        FatUtils::getInstance()->getLogger()->info("[LOGIN EVENT] from " . $e->getPlayer()->getName() . "(" . $e->getPlayer()->getUniqueId()->toString() . ") => " . ($e->isCancelled() ? "CANCELLED" : "ACCEPTED"));
    }

    /**
     * @param PlayerJoinEvent $e
     * @priority LOWEST
     */
    public function onJoin(PlayerJoinEvent $e)
    {
        $p = $e->getPlayer();
        $p->getInventory()->clearAll();

		if (!GameManager::getInstance()->isWaiting())
		{
			$e->getPlayer()->kick((new TextFormatter("template.currentlyPlaying"))->asString());
			return;
		}

        if (!PlayersManager::getInstance()->fatPlayerExist($p))
            PlayersManager::getInstance()->addPlayer($p);
        else
        {
            FatUtils::getInstance()->getLogger()->info("Reapplying player to FatPlayer");
            PlayersManager::getInstance()->getFatPlayer($p)->setPlayer($p);
        }

        new DelayedExec(function () use ($p)
		{
			PlayersManager::getInstance()->getFatPlayer($p)->updatePlayerNames();
		}, 1);
    }

    public function onQuit(PlayerQuitEvent $e)
    {
        $p = $e->getPlayer();

		$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p);
		foreach ($l_FatPlayer->getSlots() as $l_ShopItem)
		{
			if ($l_ShopItem instanceof ShopItem)
			{
				$l_ShopItem->unequip();
			}
		}

        if (GameManager::getInstance()->isWaiting())
            PlayersManager::getInstance()->removePlayer($p);
    }

	public function onPlayerChat(PlayerChatEvent $e)
	{
		if (PlayersManager::getInstance()->fatPlayerExist($e->getPlayer()))
		{
			$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($e->getPlayer());
			if ($l_FatPlayer->isMuted())
			{
				$e->getPlayer()->sendMessage(TextFormat::RED . "You've been muted until " . date("Y-m-d H:i:s", $l_FatPlayer->getMutedExpiration()));
				$e->setCancelled(true);
			} else
			{
				if ($l_FatPlayer->getPermissionGroup() === "VIP")
					$e->setMessage(TextFormat::WHITE . $e->getMessage() . TextFormat::RESET);
				else if ($l_FatPlayer->getPermissionGroup() === "Admin")
					$e->setMessage(TextFormat::GOLD . $e->getMessage() . TextFormat::RESET);
				else
					$e->setMessage(TextFormat::GRAY . $e->getMessage() . TextFormat::RESET);
			}
		}
	}

    /**
     * @priority MONITOR
     */
    public function onPlayerDamage(EntityDamageEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
        {
            if (FatPlayer::$m_OptionDisplayHealth)
            {
                new DelayedExec(function () use ($p)
				{
					PlayersManager::getInstance()->getFatPlayer($p)->updatePlayerNames();
				}, 1);
            }
        }
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerRegen(EntityRegainHealthEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
        {
            if (FatPlayer::$m_OptionDisplayHealth)
            {
                new DelayedExec(function () use ($p)
				{
					PlayersManager::getInstance()->getFatPlayer($p)->updatePlayerNames();
				}, 1);
            }
        }
    }

    public function playerDeathEvent(PlayerDeathEvent $p_Event)
    {
        $l_Player = $p_Event->getEntity();
        $l_Killer = (!is_null($l_Player->getLastDamageCause()) ? $l_Player->getLastDamageCause()->getEntity() : null);
        if ($l_Killer instanceof Player)
            GameDataManager::getInstance()->recordKill($l_Killer->getUniqueId(), $l_Player->getName());

        GameDataManager::getInstance()->recordDeath($l_Player->getUniqueId(), $l_Killer==null?"":$l_Killer->getName());
    }

    public function onPlayerTransfert(\pocketmine\event\player\PlayerTransferEvent $p_Event)
    {
        \SalmonDE\StatsPE\Base::getInstance()->getDataProvider()->savePlayer($p_Event->getPlayer());
    }

    const bedrockViewDistance = 10; // ~ the redius
    public function onInvisibleBedrockHeld(PlayerItemHeldEvent $event)
    {
        FatUtils::getInstance()->getLogger()->info("PlayerItemHeldEvent");
        if($event->getItem()->getId() == BlockIds::INVISIBLE_BEDROCK && !array_key_exists($event->getPlayer()->getUniqueId()->toString(), InvisibleBlockTask::$playerInvisibleBedrockTasks)) {
            InvisibleBlockTask::$playerInvisibleBedrockTasks[$event->getPlayer()->getUniqueId()->toString()] =
                FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new InvisibleBlockTask(FatUtils::getInstance(), $event), 20);
        }
    }
}

class InvisibleBlockTask extends \pocketmine\scheduler\PluginTask
{
    public $event;
    public static $playerInvisibleBedrockTasks = [];

    public function __construct(Plugin $owner, PlayerItemHeldEvent $event)
    {
        parent::__construct($owner);
        $this->event = $event;
        FatUtils::getInstance()->getLogger()->info("construct task");
    }

    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        if($this->event->getPlayer()->getInventory()->getItemInHand()->getId() == BlockIds::INVISIBLE_BEDROCK) {
            $player = $this->event->getPlayer();
            FatUtils::getInstance()->getLogger()->info("onRun " . $player->getName());
            for ($x = -EventListener::bedrockViewDistance; $x < EventListener::bedrockViewDistance; $x++) {
                for ($y = -EventListener::bedrockViewDistance; $y < EventListener::bedrockViewDistance; $y++) {
                    for ($z = -EventListener::bedrockViewDistance; $z < EventListener::bedrockViewDistance; $z++) {
                        $block = $player->level->getBlock(Position::fromObject($player->add($x, $y, $z), $player->level));
                        if($block->getId() == BlockIds::INVISIBLE_BEDROCK)
                        {
                            ParticleBuilder::fromParticleId(Particle::TYPE_REDSTONE)->playForPlayer(Position::fromObject($block->add(0.5, 0.5, 0.5), $player->level), $player);
                            FatUtils::getInstance()->getLogger()->info("display particle");
                        }
                    }
                }
            }
        }
        else
        {
            /** @var PluginTask $task */
            $task = InvisibleBlockTask::$playerInvisibleBedrockTasks[$this->event->getPlayer()->getUniqueId()->toString()];
            FatUtils::getInstance()->getServer()->getScheduler()->cancelTask($task->getTaskId());
            unset(InvisibleBlockTask::$playerInvisibleBedrockTasks[$this->event->getPlayer()->getUniqueId()->toString()]);
            FatUtils::getInstance()->getLogger()->info("destroy task");
        }
    }
}
