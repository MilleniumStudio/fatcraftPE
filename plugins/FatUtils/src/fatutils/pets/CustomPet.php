<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 18/10/2017
 * Time: 14:16
 */

namespace fatutils\pets;


use Exception;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class CustomPet extends Living
{
    private $m_id = 0;
    private $m_petType = "none";
    public $width = 1;
    public $height = 1;
    public $m_hasGravity = true;
    public $m_isJumper = false;
    public $m_distOffset = 0;
    public $m_offsetY = 0;
    public $m_speed = 0.3;
    public $m_climb = false;
    public $m_scale = 1;

    //todo remove, juste for testing
//    public static $test = 108;
//    public static $test2 = 90;
    public static $test = 0;
    public static $test2 = 1;
    public static $test3 = 0;

    public function __construct(Level $level, CompoundTag $nbt, string $petType, array $options = [])
    {
        $petDatas = PetTypes::ENTITIES[$petType];
        // apply originals options
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

        // add customs options
        foreach ($options as $optK => $optV) {
//            echo $optK." =>".$optV."\n";
            $option = explode("/", $optV);
            if (!$this->modifyAttributes($optK, $option[0], $option[1])) {
                echo "Error on pet construction on option \"" . $optK . ":" . $optV . "\"\n";
            }
        }

//        echo "=============\nPet config :"
//            ."\ntype: ".$petType
//            ."\nfly: ".!$this->m_hasGravity
//            ."\njump: ".$this->m_isJumper
//            ."\nclimb: ".$this->m_climb
//            ."\ndistOffset: ".$this->m_distOffset
//            ."\noffsetY: ".$this->m_offsetY
//            ."\nscale: ".$this->m_scale
//            ."\nspeed: ".$this->m_speed
//            ."\n=============\n";


        $this->m_petType = $petType;
        $this->setCanSaveWithChunk(false);
        parent::__construct($level, $nbt);
        $this->setScale($this->m_scale);
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

    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->type = $this->m_id;
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
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