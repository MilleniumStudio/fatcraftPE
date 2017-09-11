<?php
/**
 * User: Unikaz
 * Date: 11/09/2017
 */

namespace Bedwars;


use pocketmine\block\Block;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\InventoryType;
use pocketmine\inventory\SimpleTransactionGroup;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;


class Merchant implements Listener
{
    public $villager;
    public $content;
    public $plugin;


    public function __construct(PluginBase $plugin, Array $content, Location $p_location)
    {
        $this->plugin = $plugin;
        $this->plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
        $this->content = $content;
        $tag = new CompoundTag("", [
                "Pos" => new ListTag("Pos", [
                    new DoubleTag("", $p_location->getX()),
                    new DoubleTag("", $p_location->getY()),
                    new DoubleTag("", $p_location->getZ())
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
        $this->villager = new Villager($p_location->level, $tag);
        $p_location->getLevel()->addEntity($this->villager);
        $this->villager->spawnToAll();
    }

    public function onHit(EntityDamageEvent $event)
    {
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            if ($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();
                if ($damager instanceof Player) {
                    $event->setCancelled();
                    $this->openShop($damager, 0);
                }
            }
        }
    }

    public function openShop(Player $player, $page)
    {
        /** @var Chest $tile */
        $tile = null;
        $currentBlock = $player->level->getBlock(new Vector3(floor($player->x), floor($player->y) - 4, floor($player->z)));
        if ($currentBlock->getId() == Item::CHEST) {
            echo "old\n";
            $tile = $currentBlock->level->getTile(new Vector3($currentBlock->x, $currentBlock->y, $currentBlock->z));
        } else {
            echo "new\n";
            Tile::registerTile(CustomChest::class);
            $tile = Tile::createTile('CustomChest', $player->getLevel(), new CompoundTag('', [
                new StringTag('id', Tile::CHEST),
                new IntTag('ChestShop', 1),
                new IntTag('x', floor($player->x)),
                new IntTag('y', floor($player->y) - 4),
                new IntTag('z', floor($player->z))
            ]));
            $block = Block::get(Block::CHEST);
            $block->x = $tile->getFloorX();
            $block->y = $tile->getFloorY();
            $block->z = $tile->getFloorZ();
            $block->level = $tile->getLevel();
            $block->level->sendBlocks([$player], [$block]);
        }
        $this->fillInventoryWithShop($inventory = $tile->getInventory(), $page);
        $player->addWindow($inventory);

    }

    public function fillInventoryWithShop(CustomChestInventory $inventory, int $category = 0)
    {
        echo "fill\n";
        $localContent = $this->content;
        if ($category == null || $category == 0) {
            echo "category\n";
            $i = 0;
            foreach (array_keys($localContent) as $itemId) {
                $item = Item::get($itemId);
                $nbt = $item->getNamedTag() ?? new CompoundTag("", []);
                $nbt->test = new StringTag("category", "category");
                $item->setNamedTag($nbt);
                $inventory->setItem($i, $item);
                $i++;
            }
        } else {
            echo "content\n";
            if(true)return;
            $localContent = $this->content[$category];
            $i = 0;
            foreach ($localContent as $itemArray) {
                $item = Item::get($itemArray[0]);
                $inventory->setItem($i, $item);
                $i++;
            }
        }
    }

    public function onTransaction(InventoryTransactionEvent $event)
    {
        echo "#######################################\n";
        $transactionAA = $event->getTransaction();
        $player = null;
        $slot = -1;
        $item = null;
        if ($transactionAA instanceof SimpleTransactionGroup)
            $player = $transactionAA->getSource();
        foreach ($event->getTransaction()->getTransactions() as $transaction) {
            if ($transaction->getInventory()->getName() == CustomChest::customName) {
                $event->setCancelled(true);
                $slot = $transaction->getSlot();
                $item = $transaction->getSourceItem();
            }
        }
        if ($item != null && $item->getNamedTagEntry("category") == "category") {
            echo "TR : " . $player . " -> " . $item . "(" . $slot . ")\n";
            echo "ask page " . $item->getId() . "\n";
            $this->openShop($player, $item->getId());
        } else {
            echo "buy " . $item;
        }
    }
}


class CustomChestInventory extends \pocketmine\inventory\ChestInventory
{
    public function __construct(CustomChest $tile)
    {
        parent::__construct($tile, InventoryType::get(InventoryType::CHEST));
    }

    public function getName(): string
    {
        return CustomChest::customName;
    }


    public function onOpen(Player $who)
    {
        parent::onOpen($who);
    }

    public function onClose(Player $who)
    {
//        $this->holder->sendReplacement($who);
        parent::onClose($who);
//        unset(\ChestShop\Main::getInstance()->clicks[$who->getId()]);
//        $this->holder->close();
    }
}

class CustomChest extends \pocketmine\tile\Chest
{
    const customName = "Shop";
    private $replacement = [0, 0];

    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);
        $this->inventory = new CustomChestInventory($this);
        $this->replacement = [$this->getBlock()->getId(), $this->getBlock()->getDamage()];
        $this->name = CustomChest::customName;
        $this->setName(CustomChest::customName);
    }

    public function getInventory(): CustomChestInventory
    {
        return $this->inventory;
    }

    private function getReplacement(): Block
    {
        return Block::get(...$this->replacement);
    }

    public function sendReplacement(Player $player)
    {
        $block = $this->getReplacement();
        $block->x = $this->x;
        $block->y = $this->y;
        $block->z = $this->z;
        $block->level = $this->getLevel();
        if ($block->level !== null) {
            $block->level->sendBlocks([$player], [$block]);
        }
    }

    public function spawnTo(Player $player)
    {
        //needless
    }

    public function spawnToAll()
    {
        //needless
    }

    public function saveNBT()
    {
        //needless
    }
}