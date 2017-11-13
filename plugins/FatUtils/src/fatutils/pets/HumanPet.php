<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 18/10/2017
 * Time: 14:16
 */

namespace fatutils\pets;

use fatutils\FatUtils;
use Exception;
use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\command\ConsoleCommandSender;

class HumanPet extends Human
{
    private $m_id = 0;
    private $m_petType = "none";
    public $width = 1;
    public $height = 1;
    public $m_hasGravity = false;
    public $m_isJumper = false;
    public $m_distOffset = 0;
    public $m_offsetY = 0;
    public $m_speed = 0.3;
    public $m_climb = false;
    public $m_scale = 1;

    //todo remove, just for testing
//    public static $test = 108;
//    public static $test2 = 90;
    public static $test = 0;
    public static $test2 = 1;
    public static $test3 = 0;

    public $function = null;
    public $data = array();

    public function __construct(Level $level, CompoundTag $nbt, string $petType, array $options = [])
    {
        $petDatas = PetTypes::ENTITIES[$petType];
//        // apply originals options
        $this->m_id = $petDatas["id"];
        $this->width = $petDatas["width"];
        $this->height = $petDatas["height"];
        if (array_key_exists("fly", $petDatas))
            $this->m_hasGravity = false;
        if (array_key_exists("jump", $petDatas))
            $this->m_isJumper = true;
        if (array_key_exists("distOffset", $petDatas))
            $this->m_distOffset = $petDatas["distOffset"];
        if (array_key_exists("speed", $petDatas))
            $this->m_speed = $petDatas["speed"];
        if (array_key_exists("offsetY", $petDatas))
            $this->m_offsetY = $petDatas["offsetY"];
        if (array_key_exists("climb", $petDatas))
            $this->m_climb = $petDatas["climb"];
        if (array_key_exists("scale", $petDatas))
            $this->m_scale = $petDatas["scale"];
        if (array_key_exists("color", $petDatas))
            $this->m_color = $petDatas["color"];
//
//        // add customs options
        foreach ($options as $optK => $optV) {
//            echo $optK." =>".$optV."\n";
            $option = explode("/", $optV);
            if (!$this->modifyAttributes($optK, $option[0], $option[1])) {
                echo "Error on pet construction on option \"" . $optK . ":" . $optV . "\"\n";
            }
        }

        $this->m_petType = $petType;
        $this->setCanSaveWithChunk(false);

        parent::__construct($level, $nbt);
        if(!isset($this->namedtag->NameVisibility)) {
            $this->namedtag->NameVisibility = new IntTag("NameVisibility", 2);
        }
        switch ($this->namedtag->NameVisibility->getValue()) {
            case 0:
                $this->setNameTagVisible(false);
                $this->setNameTagAlwaysVisible(false);
                break;
            case 1:
                $this->setNameTagVisible(true);
                $this->setNameTagAlwaysVisible(false);
                break;
            case 2:
                $this->setNameTagVisible(true);
                $this->setNameTagAlwaysVisible(true);
                break;
            default:
                $this->setNameTagVisible(true);
                $this->setNameTagAlwaysVisible(true);
                break;
        }
        if(!isset($this->namedtag->Scale)) {
                $this->namedtag->Scale = new FloatTag("Scale", 1.0);
        }
        $this->setDataProperty(self::DATA_SCALE, self::DATA_TYPE_FLOAT, $this->namedtag->Scale->getValue());
        $this->setDataProperty(self::DATA_FLAG_NO_AI, self::DATA_TYPE_BYTE, 1, true);

        $this->initEntity();
    }

    public function modifyAttributes(string $attributeName, string $valueType, $value): bool
    {
        try {
            $attr = constant("self::" . $attributeName);
            $type = constant("self::" . $valueType);

            switch ($type) {
                case 0: { //byte
                    $value = $value + 0; //le cast pas sérieux qui est plus efficace que le cast correct (et toujours mieux que l'absence de cast)
                }
                    break;
                case 1: { //short
                    $value = intval($value);
                }
                    break;
                case 2: { //int
                    $value = intval($value);
                }
                    break;
                case 3: { //float
                    $value = floatval($value);
                }
                    break;
                case 4: { //string
                    // nothing to do
                }
                    break;
                case 5: { //slot ?
                    echo "Error for attribute ".$attributeName.": type SLOT isn't implemented\n";
                }
                    break;
                case 6: { //pos ?
                    echo "Error for attribute " . $attributeName . ": type POS isn't implemented\n";
                }
                    break;
                case 7: { //long
                    $value = $value + 0;
                }
                    break;
                case 8: { //Vec3F
                    echo "Error for attribute " . $attributeName . ": type VECTOR3F isn't implemented\n";
                }
            }
            $this->setDataProperty($attr, $type, $value, true);

        } catch (Exception $exception) {
            return false;
        }
        return true;
    }

    public function onTick(int $currentTick)
    {
        if ($this->function !== null)
        {
            $this->function->onTick($currentTick);
        }
    }

    public function onInterract(Player $player)
    {
        if ($this->function !== null)
        {
            $this->function->onInterract($player);
        }
        if(isset($this->namedtag->Commands))
        {
            foreach ($this->namedtag->Commands as $cmd)
            {
                FatUtils::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $cmd));
            }
        }
    }

    protected function sendSpawnPacket(Player $player) : void{
        if(!$this->skin->isValid()){
            throw new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
        }

        $pk = new AddPlayerPacket();
        $pk->uuid = $this->getUniqueId();
        $pk->username = $this->getName();
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->item = $this->getInventory()->getItemInHand();
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        $this->inventory->sendArmorContents($player);

//        if(!($this instanceof Player)){
            $this->sendSkin([$player]);
//        }
    }


    public function getName(): string
    {
        return $this->m_petType;
    }

    protected function applyGravity()
    {
        if ($this->m_hasGravity)
            parent::applyGravity();
    }

}