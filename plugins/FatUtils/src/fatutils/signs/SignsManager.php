<?php

namespace fatutils\signs;

use fatutils\FatUtils;
use fatutils\tools\WorldUtils;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign as TileSign;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\block\BlockIds;
use pocketmine\block\BlockFactory;

class SignsManager implements Listener, CommandExecutor
{
    private static $m_Instance = null;
    public $config;
    private $m_RegisteredSigns = array();

    public static function getInstance(): SignsManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new SignsManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new OnTick(FatUtils::getInstance()), 1);
        $this->loadConfigs();
    }

    public function loadConfigs()
    {
        FatUtils::getInstance()->getLogger()->info("[Signs] Loading signs.yml");
        FatUtils::getInstance()->saveResource("signs.yml");
        $this->config = new Config(FatUtils::getInstance()->getDataFolder() . "signs.yml");
        if ($this->config == null)
        {
            return;
        }
        foreach ($this->config->get("signs") as $key => $value)
        {
            $name = isset($value['name']) ? $value['name'] : null;
            if ($name == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Signs] Error: sign without name");
                continue;
            }
            $p_RawLocation = isset($value['location']) ? $value['location'] : null;
            $p_RawType = isset($value['location']) ? $value['location'] : "WALL_SIGN";
            $p_RawSide = isset($value['location']) ? $value['location'] : "SIDE_WEST";
            if ($p_RawLocation == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Signs] Error: sign ". $name . " has no/bad location");
                continue;
            }
            $l_Location = WorldUtils::stringToLocation($p_RawLocation);
            if ($l_Location->level == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Signs] Error: npc ". $name . " world " . $p_RawLocation . " not found");
                continue;
            }

            $p_Type = null;
            $p_Side = WorldUtils::getSideFromString($p_RawSide);

            //Optionnal
            $update = isset($value['update']) ? $value['update'] : false;
            $text = isset($value['text']) ? $value['text'] : ["", "", "", ""];
            $commands = isset($value['commands']) ? $value['commands'] : [];

            $sign = $this->spawnSign($l_Location, $p_Type, $p_Side, $name, $text, $commands);

            if ($update)
            {
                $sign->namedtag->Update = true;
            }
//            $this->updateSign($sign);
        }
    }

    public function spawnSign(Location $p_Location, $type, $face, $name, array $text = ["", "", "", ""], array $commands = []): Tile
    {
        $nbt = new CompoundTag("", [
            new StringTag("id", $name),
            new IntTag("x", (int) $p_Location->x),
            new IntTag("y", (int) $p_Location->y),
            new IntTag("z", (int) $p_Location->z)
        ]);
        for($i = 1; $i <= 4; ++$i){
            $nbt->setString(sprintf(TileSign::TAG_TEXT_LINE, $i), "");
        }

        $nbt->Commands = new CompoundTag("Commands", []);
        $nbt->Update = new ByteTag("Update", false);
        echo "set nbt\n";

//        $block = $p_Location->getLevel()->getBlockAt($p_Location->x, $p_Location->y, $p_Location->z);
//        $p_Location->getLevel()->setBlock($block, BlockFactory::get(BlockIds::WALL_SIGN, 0), true);

        $tile = Tile::createTile(Tile::SIGN, $p_Location->getLevel(), $nbt);
        echo "tile created\n";
        $tile->setText($text[0], $text[1], $text[2], $text[3]);
        echo "set text\n";

        foreach ($commands as $command)
        {
            $tile->namedtag->Commands[$command] = new StringTag($command, $command);
        }
        $this->m_RegisteredSigns[$name] = $tile;
        $tile->spawnToAll();
        FatUtils::getInstance()->getLogger()->info("[Signs] Spawned sign tile " . $tile->getId() . " !");
        return $tile;
    }

    public function updateSigns(int $currentTick)
    {

    }

    public function updateSign(Tile $p_Tile)
    {
        FatUtils::getInstance()->getLogger()->info("[Signs] update " . $p_Tile->getId());
//        $p_Tile->sendData($p_Tile->getViewers());
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
        if ($sender instanceof Player) {
            switch ($args[0]) {
                case "spawn": {
                    $entity = $this->spawnSign($sender, BlockIds::SIGN_POST, Vector3::SIDE_SOUTH, $args[1]);
                    $sender->sendMessage("NPC " . $entity->getId() . " spawned !");
                }
                    break;
                case "rm": {
                }
                    break;
                case "tp": {
                }
                    break;
                case "list": {
                    $sender->sendMessage("Signs list :");
                    foreach ($this->m_RegisteredSigns as $l_Sign)
                    {
                        $sender->sendMessage(" - " . $l_Sign->getId() . " " . "");
                    }
                }
                    break;
                case "stop": {
                }
                    break;
            }
        } else {
            echo "Commands only available as a player\n";
        }
        return true;
    }

    public function onPlayerPeLoginEvent(\pocketmine\event\player\PlayerPreLoginEvent $p_Event)
    {
//        \fatutils\tools\SkinUtils::saveSkin($p_Event->getPlayer()->getSkin(), FatUtils::getInstance()->getDataFolder() . "skins/" . $p_Event->getPlayer()->getName() . ".png");
    }

    /**
        * @param EntityDamageEvent $event
        * @ignoreCancelled true
        *
        * @return void
        */
//    public function onEntityDamage(EntityDamageEvent $event)
//    {
//        if(!$event instanceof EntityDamageByEntityEvent) {
//            return;
//        }
//        if(!$event->getDamager() instanceof Player) {
//            return;
//        }
//        if(isset($event->getEntity()->namedtag->Commands))
//        {
//            $event->setCancelled(true);
//            foreach ($event->getEntity()->namedtag->Commands as $cmd) {
//                FatUtils::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $event->getDamager()->getName(), $cmd));
//            }
//        }
//    }
}

//===============================================
class OnTick extends PluginTask
{
    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        SignsManager::getInstance()->updateSigns($currentTick);
    }
}