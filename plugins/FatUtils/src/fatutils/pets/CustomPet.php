<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 18/10/2017
 * Time: 14:16
 */

namespace fatutils\pets;


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


        $this->m_petType = $petType;
        $this->setCanSaveWithChunk(false);
        parent::__construct($level, $nbt);
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
        if($this->m_hasGravity)
            parent::applyGravity();
    }



}