<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 13:51
 */

namespace fatcraft\lobby;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\shop\ShopManager;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use fatutils\tools\WorldUtils;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\ui\impl\GamesWindow;
use fatutils\ui\impl\LobbiesWindow;
use fatutils\shop\ShopItem;
use fatutils\ui\impl\ScaleWindow;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\level\ChunkUnloadEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use fatutils\holograms\HologramsManager;

class Lobby extends PluginBase implements Listener
{
    private static $m_Instance;
    private $m_SpawnPoint = null;

    public static function getInstance(): Lobby
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
        $this->getCommand("spawn")->setExecutor($this);
        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        WorldUtils::stopWorldsTime();
        WorldUtils::setWorldsTime(15000); // = 12h * 3600 seconds * 20 ticks (day = 864000)
        HologramsManager::getInstance();

        if ($this->getConfig()->exists("spawn"))
        {
            $this->m_SpawnPoint = WorldUtils::stringToLocation($this->getConfig()->getNested("spawn"));
            $this->m_SpawnPoint->getLevel()->setSpawnLocation($this->m_SpawnPoint);
        }

        FatPlayer::$m_OptionDisplayHealth = false;
        ShopManager::$m_OptionAutoEquipSavedItems = true;

        Sidebar::getInstance()
			->addTranslatedLine(new TextFormatter("lobby.sidebar.header"))
			->addWhiteSpace()
			->addTranslatedLine(new TextFormatter("lobby.sidebar.money"))
			->addMutableLine(function (Player $p_Player) {
				$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);
				return [
					$l_FatPlayer->getFatsilver() . " " . (new TextFormatter("currency.fatsilver.short"))->asStringForFatPlayer($l_FatPlayer),
					$l_FatPlayer->getFatgold() . " " . (new TextFormatter("currency.fatgold.short"))->asStringForFatPlayer($l_FatPlayer)
				];
			});

        LoadBalancer::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "buycraft secret c3ff65408c433494f06bcd411bc6399e03fb6c6c");
    }

    public function onPlayerJoin(PlayerJoinEvent $e)
    {
        $l_Player = $e->getPlayer();

        new DelayedExec(function () use ($e)
		{
			$e->getPlayer()->addTitle(
				(new TextFormatter("lobby.welcome.title"))->asStringForPlayer($e->getPlayer()),
				(new TextFormatter("lobby.welcome.subtitle", ["name" => $e->getPlayer()->getName()]))->asStringForPlayer($e->getPlayer())
			);
		}, 5);

        // Items in player bar
        if ($l_Player->hasPermission("lobby.quickgameselection"))
        {
            $l_Player->getInventory()->setHeldItemIndex(4);

            $l_MainMenu = Item::get(ItemIds::COMPASS);
			$l_MainMenu->setCustomName((new TextFormatter("lobby.hotbar.mainMenu"))->asStringForPlayer($l_Player));
            $l_Player->getInventory()->setItem(2, $l_MainMenu);
		}
        if ($l_Player->hasPermission("lobby.fly"))
        {
            $l_FlyItem = Item::get(ItemIds::ELYTRA);
            $l_FlyItem->setCustomName((new TextFormatter("lobby.hotbar.lobbyFly"))->asStringForPlayer($l_Player));
            $l_Player->getInventory()->setItem(8, $l_FlyItem);
        }
		if ($l_Player->hasPermission("effect.superjump"))
        {
            $l_Player->addEffect(Effect::getEffect(Effect::JUMP_BOOST)->setAmplifier(2)->setDuration(INT32_MAX));
        }
        if ($l_Player->hasPermission("lobby.setscale"))
        {
            $l_FlyItem = Item::get(ItemIds::TOTEM);
            $l_FlyItem->setCustomName((new TextFormatter("lobby.hotbar.setscale"))->asStringForPlayer($l_Player));
            $l_Player->getInventory()->setItem(4, $l_FlyItem);
        }

		$l_Shop = Item::get(ItemIds::EMERALD);
		$l_Shop->setCustomName((new TextFormatter("shop.title"))->asStringForPlayer($l_Player));
        $l_Player->getInventory()->setItem(1, $l_Shop);

		$l_LobbyChooser = Item::get(ItemIds::NETHERSTAR);
		$l_LobbyChooser->setCustomName((new TextFormatter("lobby.hotbar.lobbyChooser"))->asStringForPlayer($e->getPlayer()));
        $l_Player->getInventory()->setItem(6, $l_LobbyChooser);

        $l_Player->getInventory()->sendContents($e->getPlayer());
        $l_Player->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(2)->setDuration(INT32_MAX));

        if ($this->m_SpawnPoint != null)
        {
            $l_Player->teleport($this->m_SpawnPoint, $this->m_SpawnPoint->yaw, $this->m_SpawnPoint->pitch);
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $p_Event)
	{
		$l_PlayerManager = PlayersManager::getInstance();
		/*$l_FatPlayer = $l_PlayerManager->getFatPlayer($p_Event->getPlayer());

		$l_Slot = $l_FatPlayer->getSlot(ShopItem::SLOT_PET);

		if ($l_Slot != null)
		    $l_Slot->unequip();*/

		$l_PlayerManager->removeFatPlayer($p_Event->getPlayer());
	}

    // actions on item select / touch
    public function onPlayerUseItem(PlayerInteractEvent $p_Event)
    {
        switch ($p_Event->getItem()->getId())
        {
            case ItemIds::COMPASS:
                new GamesWindow($p_Event->getPlayer());
                break;
            case ItemIds::NETHERSTAR:
                new LobbiesWindow($p_Event->getPlayer());
                break;
            case ItemIds::EMERALD:
                ShopManager::getInstance()->getShopMenu($p_Event->getPlayer())->open();
                break;
            case ItemIds::ELYTRA:
                $p_Event->getPlayer()->getInventory()->setChestplate(ItemFactory::get(ItemIds::ELYTRA));
                $p_Event->getPlayer()->teleport(new Vector3(0.5, 56, 66.5), 180);
                break;
            case ItemIds::TOTEM:
                new ScaleWindow($p_Event->getPlayer());
                break;
        }
    }

    // disable all inventory items move
    public function onInventoryTransaction(InventoryTransactionEvent $p_Event)
    {
        if (!$p_Event->getTransaction()->getSource()->isOp())
            $p_Event->setCancelled(true);
    }

    public function onItemPickup(InventoryPickupItemEvent $p_Event)
    {
        if (!$p_Event->getInventory()->getHolder()->isOp())
            $p_Event->setCancelled(true);
    }

    public function onArrowPickup(InventoryPickupArrowEvent $p_Event)
    {
        if (!$p_Event->getInventory()->getHolder()->isOp())
            $p_Event->setCancelled(true);
    }

    public function onPlayerDropItem(PlayerDropItemEvent $p_Event)
    {
        if (!$p_Event->getPlayer()->isOp())
            $p_Event->setCancelled(true);
    }

    public function onPlayerExhaust(PlayerExhaustEvent $p_Event)
    {
        $p_Event->setCancelled(true);
    }

    public function onPlayerDamage(EntityDamageEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
        {
            $e->setCancelled(true);
            if ($e->getCause() == EntityDamageEvent::CAUSE_VOID)
            {
                if ($this->m_SpawnPoint != null)
                {
                    $p->getPlayer()->setHealth(20);
                    $p->getPlayer()->teleport($this->m_SpawnPoint, $this->m_SpawnPoint->yaw, $this->m_SpawnPoint->pitch);
                }
            }
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $p_Event)
    {
        if ($this->m_SpawnPoint != null)
        {
            $p_Event->getPlayer()->setHealth(20);
            $p_Event->getPlayer()->teleport($this->m_SpawnPoint, $this->m_SpawnPoint->yaw, $this->m_SpawnPoint->pitch);
        }
    }

    /**
     * @param \pocketmine\command\CommandSender $sender
     * @param \pocketmine\command\Command $command
     * @param string $label
     * @param string[] $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player)
        {
            $sender->teleport($this->m_SpawnPoint, $this->m_SpawnPoint->yaw, $this->m_SpawnPoint->pitch);
        }
        else
        {
            echo "Commands only available as a player\n";
        }
        return true;
    }


    public function onChunkUnload(ChunkUnloadEvent $p_event)
    {
        $p_event->setCancelled();
    }
}