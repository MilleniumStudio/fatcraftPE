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

    public function __construct(Level $level, CompoundTag $nbt, string $petType)
    {
        $this->m_id = PetTypes::ENTITIES[$petType];
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



}