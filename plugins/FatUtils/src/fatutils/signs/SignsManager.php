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
use pocketmine\event\player\PlayerInteractEvent;
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
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use fatutils\tools\schedulers\LoopedExec;

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
        new LoopedExec([$this, "updateSigns"]);
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
            if ($p_RawLocation == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Signs] Error: sign ". $name . " has no/bad location");
                continue;
            }
            $l_Location = WorldUtils::stringToLocation($p_RawLocation);
            if ($l_Location->level == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Signs] Error: sign ". $name . " world " . $p_RawLocation . " not found");
                continue;
            }
            $face = isset($value['face']) ? $value['face'] : null;

            $tile = $this->getSignAt($l_Location);

            if ($tile == null && $face != null)
            {
                $tile = $this->fixSignAt($l_Location, $face);
            }

            if ($tile !== null)
            {
                //Optionnal
                $update = isset($value['update']) ? $value['update'] : false;
                $text = isset($value['text']) ? $value['text'] : ["", "", "", ""];
                $commands = isset($value['commands']) ? $value['commands'] : [];
                $function = isset($value['function']) ? $value['function'] : "";
                $data = isset($value['data']) ? $value['data'] : [];

                $sign = new CustomSign($name, $tile);
                $tile->namedtag->setString("SignName", $name);
                $sign->update = $update;
                $sign->text = $text;
                $sign->commands = $commands;
                $sign->data = $data;

                try
                {
                    switch ($function)
                    {
                        case "SignFunctionServer":
                            $sign->function = new functions\SignFunctionServer($sign);
                            break;
                        case "SignFunctionRandomServer":
                            $sign->function = new functions\SignFunctionRandomServer($sign);
                            break;
                        case "SignFunctionCounter":
                            $sign->function = new functions\SignFunctionCounter($sign);
                            break;
                        case "NPCFunctionTeleport":
                            $sign->function = new functions\SignFunctionCounter($sign);
                            break;

                        default:
                            break;
                    }
                } catch (Exception $ex)
                {
                    FatUtils::getInstance()->getLogger()->warning("[Signs] ". $ex->getMessage());
                }

                $this->m_RegisteredSigns[$name] = $sign;
            }
            else
            {
                FatUtils::getInstance()->getLogger()->warning("[Signs] Error: no sign ". $name . " found in " . $p_RawLocation . "");
            }
        }

        foreach ($this->config->get("signsareas") as $key => $value)
        {
            $name = isset($value['name']) ? $value['name'] : null;
            if ($name == null)
            {
                FatUtils::getInstance()->getLogger()->warning("[Signs] Error: signsarea without name");
                continue;
            }
            $p_RawLocations = isset($value['locations']) ? $value['locations'] : [];
            if (count($p_RawLocations) == 0)
            {
                FatUtils::getInstance()->getLogger()->warning("[Signs] Error: signsarea ". $name . " has no/bad location");
                continue;
            }
            $face = isset($value['face']) ? $value['face'] : null;

            //Optionnal
            $update = isset($value['update']) ? $value['update'] : false;
            $text = isset($value['text']) ? $value['text'] : ["", "", "", ""];
            $commands = isset($value['commands']) ? $value['commands'] : [];
            $function = isset($value['function']) ? $value['function'] : "";
            $data = isset($value['data']) ? $value['data'] : [];

            $tiles = array();
            $i = 0;
            foreach ($p_RawLocations as $p_RawLocation)
            {
                $l_Location = WorldUtils::stringToLocation($p_RawLocation);
                if ($l_Location->level == null)
                {
                    FatUtils::getInstance()->getLogger()->warning("[Signs] Error: signsarea ". $name . " world " . $p_RawLocation . " not found");
                    continue;
                }

                $tile = $this->getSignAt($l_Location);

                if ($tile == null && $face != null)
                {
                    $tile = $this->fixSignAt($l_Location, $face);
                }

                if ($tile !== null)
                {
                    $tile->namedtag->setString("SignName", $name);
                    $tile->namedtag->setInt("Index", $i);

                    $sign = new CustomSign($name, $tile);
                    $sign->update = $update;
                    $sign->text = $text;

                    $tiles[] = $sign;
                    $i++;
                }
                else
                {
                    FatUtils::getInstance()->getLogger()->warning("[Signs] Error: signsarea ". $name . " : no sign found in " . $p_RawLocation . "");
                }
            }

            if (count($tiles))
            {
                $multipleSign = new MultipleSigns($name, $tiles);
                $multipleSign->update = $update;
                $multipleSign->commands = $commands;
                $multipleSign->data = $data;

                try
                {
                    switch ($function)
                    {
                        case "MultiSignFunctionServer":
                            $multipleSign->function = new functions\MultiSignFunctionServer($multipleSign);
                            break;
                        case "MultiSignFunctionRandomServer":
                            $multipleSign->function = new functions\MultiSignFunctionRandomServer($multipleSign);
                            break;
                        case "MultiSignFunctionTeleport":
                            $multipleSign->function = new functions\MultiSignFunctionTeleport($multipleSign);
                            break;

                        default:
                            break;
                    }
                } catch (Exception $ex)
                {
                    FatUtils::getInstance()->getLogger()->warning("[Signs] ". $ex->getMessage());
                }

                $this->m_RegisteredSigns[$name] = $multipleSign;
            }
        }
    }

    public function getSignAt(Location $p_Location) : ?TileSign
    {
        $block = $p_Location->getLevel()->getBlockAt($p_Location->x, $p_Location->y, $p_Location->z);
        if ($block->getId() == Block::SIGN_POST OR $block->getId() == Block::WALL_SIGN)
        {
            $tile = $block->getLevel()->getTile($block);

            if ($tile instanceof TileSign)
            {
                return $tile;
            }
        }
        return null;
    }

    public function fixSignAt(Location $p_Location, $face) : ?TileSign
    {
        $side = \fatutils\tools\WorldUtils::getSideFromString($face);
        $oldblock = $p_Location->getLevel()->getBlockAt($p_Location->x, $p_Location->y, $p_Location->z);
        $p_Location->getLevel()->setBlock($oldblock, BlockFactory::get(Block::WALL_SIGN, $side), true);
        $block = $p_Location->getLevel()->getBlockAt($p_Location->x, $p_Location->y, $p_Location->z);
        if ($block->getId() == Block::SIGN_POST OR $block->getId() == Block::WALL_SIGN)
        {
            $tile = $block->getLevel()->getTile($block);

            if ($tile instanceof TileSign)
            {
                $tile->namedtag->setString(TileSign::TAG_TEXT_BLOB, implode("\n", array("", "", "", "")));
                FatUtils::getInstance()->getLogger()->warning("[Signs] sign fixed on ". \fatutils\tools\WorldUtils::locationToString($p_Location));
                $tile->spawnToAll();
                return $tile;
            }
        }
        return null;
    }

    public function updateSigns()
    {
        $currentTick = FatUtils::getInstance()->getServer()->getTick();
        foreach ($this->m_RegisteredSigns as $l_Sign)
        {
            $l_Sign->onTick($currentTick);
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
        if ($sender instanceof Player) {
            switch ($args[0]) {
                case "spawn": {
//                    $entity = $this->spawnSign($sender, BlockIds::SIGN_POST, Vector3::SIDE_SOUTH, $args[1]);
//                    $sender->sendMessage("NPC " . $entity->getId() . " spawned !");
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
                        $sender->sendMessage(" - " . $l_Sign->name . " " . "");
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

    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($block->getId() == Block::SIGN_POST OR $block->getId() == Block::WALL_SIGN)
        {
            $tile = $block->getLevel()->getTile($block);

            if ($tile instanceof TileSign)
            {
                FatUtils::getInstance()->getLogger()->debug("[Signs] Text interact " . $block->getName() . " " . $tile->x . "/" . $tile->y . "/" . $tile->z . " face: " . $block->getDamage());
                if ($tile->namedtag->getString("SignName") !== null)
                {
                    if (isset($this->m_RegisteredSigns[$tile->namedtag->getString("SignName")]))
                    {
                        $sign = $this->m_RegisteredSigns[$tile->namedtag->getString("SignName")];
                        $index = -1;
                        if ($tile->namedtag->getInt("Index") !== null)
                        {
                            $index = $tile->namedtag->getInt("Index");
                        }
                        $sign->onInterract($player, $index);
                        $event->setCancelled(TRUE);
                    }
                }
            }
        }
    }
}