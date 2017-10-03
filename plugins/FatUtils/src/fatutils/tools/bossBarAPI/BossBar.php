<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 12/09/2017
 * Time: 18:01
 */

namespace fatutils\tools\bossBarAPI;


use fatutils\FatUtils;
use fatutils\tools\bossBarAPI\BossBarValues;
use fatutils\tools\TextFormatter;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\Player;

class BossBar
{
    private $m_Eid = null;
    private $m_Players = null;

    /** @var string */
    private $m_Title = "";

    /** @var float */
    private $m_Ratio = 1.0;

    /** @var int */
    private $m_Type = BossEventPacket::TYPE_SHOW;
    /** @var int */
    private $m_Color = 0;

    /**
     * BossBar constructor.
     */
    public function __construct()
    {
        if (is_null($this->m_Eid))
            $this->m_Eid = Entity::$entityCount++;
    }

    public function addPlayer(Player $p_Player):BossBar
    {
        if (is_null($this->m_Players))
            $this->m_Players = [];

        $this->m_Players[] = $p_Player;

        $packet = $this->getSpawnEntityPacket($p_Player->getLocation(), $this->getTitle($p_Player));
        $p_Player->dataPacket($packet);
        return $this;
    }

    public function addPlayers(array $p_Players):BossBar
    {
        foreach ($p_Players as $l_Player)
        {
            if ($l_Player instanceof Player)
                $this->addPlayer($l_Player);
        }
        return $this;
    }

    public function removePlayer(Player $p_Player):BossBar
    {
        $this->m_Players = array_diff($this->m_Players, [$p_Player]);
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = $this->getEntityId();
        $p_Player->dataPacket($pk);
        return $this;
    }

    /**
     * @param string|TextFormatter $p_Title
     * @return BossBar
     */
    public function setTitle($p_Title):BossBar
    {
        $this->m_Title = $p_Title;

        foreach (($this->m_Players ?? FatUtils::getInstance()->getServer()->getOnlinePlayers()) as $l_Player)
        {
            $npk = new SetEntityDataPacket(); // change name of fake wither -> bar text
            $npk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->getTitle($l_Player)]];
            $npk->entityRuntimeId = $this->getEntityId();
            FatUtils::getInstance()->getServer()->broadcastPacket([$l_Player], $npk);
        }

        $this->updateBossBar();
        return $this;
    }

    //Not Working
//    public function setType(int $p_Type):BossBar
//    {
//        $this->m_Type = $p_Type;
//        return $this;
//    }

    //Not Working
//    public function setColor(int $p_Color):BossBar
//    {
//        $this->m_Color = $p_Color;
//        return $this;
//    }

    public function setPercentage(float $p_Percent):BossBar
    {
        $this->setRatio($p_Percent / 100);
        return $this;
    }

    public function setRatio(float $p_Ratio):BossBar
    {
        $this->m_Ratio = $p_Ratio;

        $upk = new UpdateAttributesPacket(); // Change health of fake wither -> bar progress
        $upk->entries[] = new BossBarValues(1, 1000, $p_Ratio * 1000, 'minecraft:health'); // Ensures that the number is between 1 and 100; //Blame mojang, Ender Dragon seems to die on health 1
        $upk->entityRuntimeId = $this->getEntityId();
        $this->broadcastPacketToPlayers($upk);

        $this->updateBossBar();
        return $this;
    }

    public function remove()
    {
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = $this->getEntityId();
        $this->broadcastPacketToPlayers($pk);
        return $this;
    }

    //-----------
    // INTERNAL UTILS
    //-----------
    public function broadcastPacketToPlayers(DataPacket $packet){
        FatUtils::getInstance()->getServer()->broadcastPacket(($this->m_Players ?? FatUtils::getInstance()->getServer()->getOnlinePlayers()), $packet);
    }

    private function getSpawnEntityPacket(Position $p_Position, string $p_Name):AddEntityPacket
    {
        $packet = new AddEntityPacket();
        $packet->entityRuntimeId = $this->getEntityId();
        $packet->type = 52;
        $pos = $p_Position->subtract(0, 28);
        $packet->x = $pos->x;
        $packet->y = $pos->y;
        $packet->z = $pos->z;
        $packet->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $p_Name], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];

        return $packet;
    }

    private function updateBossBar()
    {
        $bpk = new BossEventPacket(); // This updates the bar
        $bpk->bossEid = $this->getEntityId();
        $bpk->eventType = $this->m_Type;
        $bpk->title = $this->getTitle();
        $bpk->healthPercent = $this->m_Ratio;
        $bpk->unknownShort = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
        $bpk->color = $this->m_Color;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
        $bpk->overlay = 0;//TODO: remove. Shoghi deleted that unneeded mess that was copy-pasted from MC-JAVA
        $bpk->playerEid = 0;//TODO TEST!!!
        $this->broadcastPacketToPlayers($bpk);
    }

    //-------------
    // GETTERS
    //-------------
    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->m_Eid;
    }

    public function getTitle(Player $p_Player = null): string
    {
        if ($this->m_Title instanceof TextFormatter)
            return $this->m_Title->asStringForPlayer($p_Player);
        return $this->m_Title;
    }

    /**
     * @return float
     */
    public function getRatio(): float
    {
        return $this->m_Ratio;
    }

}