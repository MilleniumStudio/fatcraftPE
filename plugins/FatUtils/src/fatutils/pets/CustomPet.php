<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 18/10/2017
 * Time: 14:16
 */

namespace fatutils\pets;


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

    public function __construct(Level $level, CompoundTag $nbt, string $petType)
    {
        $petDatas = PetTypes::ENTITIES[$petType];
        $this->m_id = $petDatas["id"];
        $this->width = $petDatas["width"];
        $this->height = $petDatas["height"];
        if(array_key_exists("fly", $petDatas))
            $this->m_hasGravity =  false;
        if(array_key_exists("jump", $petDatas))
            $this->m_isJumper = true;
        if(array_key_exists("distOffset", $petDatas))
            $this->m_distOffset = $petDatas["distOffset"];
        if(array_key_exists("speed", $petDatas))
            $this->m_speed = $petDatas["speed"];
        if(array_key_exists("offsetY", $petDatas))
            $this->m_offsetY = $petDatas["offsetY"]; //todo set the offset on pets config
        if(array_key_exists("climb", $petDatas))
            $this->m_climb = $petDatas["climb"]; //todo set the climb on pets config
        if(array_key_exists("scale", $petDatas))
            $this->m_scale = $petDatas["scale"];
        if(array_key_exists("color", $petDatas))
            $this->m_color = $petDatas["color"];



        //todo there's something else.... but i forgot what...

        echo "=============\nPet config :"
            ."\ntype: ".$petType
            ."\nfly: ".!$this->m_hasGravity
            ."\njump: ".$this->m_isJumper
            ."\nclimb: ".$this->m_climb
            ."\ndistOffset: ".$this->m_distOffset
            ."\noffsetY: ".$this->m_offsetY
            ."\nscale: ".$this->m_scale
            ."\nspeed: ".$this->m_speed
            ."\n=============\n";

        //todo test
//        $this->setDataProperty(CustomPet::$test, Entity::DATA_TYPE_BYTE, 2, true);
//        $this->dataProperties[CustomPet::$test] = [Entity::DATA_TYPE_BYTE, CustomPet::$test2];


        $this->m_petType = $petType;
        $this->setCanSaveWithChunk(false);
        parent::__construct($level, $nbt);
        $this->setScale($this->m_scale);

//        $this->setDataProperty(Entity::DATA_VARIANT, Entity::DATA_TYPE_INT, CustomPet::$test3, true);
        //Entity::DATA_MARK_VARIANT //for horse combination ?

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
//        $pk->metadata = CustomPet::$test2;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }


    public function getName(): string
    {
        return $this->m_petType;
    }

    protected function applyGravity()
    {
        if($this->m_hasGravity)
            parent::applyGravity();
    }



}