<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 19:01
 */

namespace fatutils\teams;


use fatutils\FatUtils;
use fatutils\players\PlayersManager;
use fatutils\spawns\Spawn;
use fatutils\tools\ColorUtils;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;

class Team
{
    private $m_Name = "NoName";
    private $m_Color = ColorUtils::WHITE;
    private $m_Prefix = "";

    /* array of Player's uuid as binary */
    public $m_Players = [];
    private $m_MaxPlayer = 10;

    private $m_Spawn = null;

    public function addPlayer(Player $p_Player)
    {
        $this->m_Players[] = $p_Player->getUniqueId()->toBinary();
    }

    public function addPlayers(array $p_Players)
    {
        foreach ($p_Players as $l_Player) {
            if ($l_Player instanceof Player)
                $this->addPlayer($l_Player);
        }
    }

    public function removePlayer(Player $p_player){
        array_splice($this->m_Players, array_search($p_player->getUniqueId()->toBinary(), $this->m_Players), 1);
    }

    public function getAlivePlayerLeft(): int
    {
        $i = 0;
        foreach ($this->getOnlinePlayers() as $l_Player) {
            $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($l_Player);
            if (!$l_FatPlayer->hasLost())
                $i++;
        }
        return $i;
    }


    public function getAlivePlayers(): array
    {
        $l_Ret = [];
        foreach ($this->getOnlinePlayers() as $l_Player) {
            $l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($l_Player);
            if (!$l_FatPlayer->hasLost())
                $l_Ret[] = $l_FatPlayer;
        }
        return $l_Ret;
    }

    public function getPlayersNames(): array
    {
        $array = [];
        foreach ($this->m_Players as $playerBinUUID) {
            $player = PlayersManager::getInstance()->getPlayerFromUUID(UUID::fromBinary($playerBinUUID));
            if ($player != null && $player instanceof Player)
                $array[] = $player->getName();
            else
                echo "No player for " . $playerBinUUID . " -> " . (UUID::fromBinary($playerBinUUID)) . "\n";
        }
        return $array;
    }

    public function isPlayerInTeam(Player $p_Player)
    {
        return in_array($p_Player->getUniqueId()->toBinary(), $this->m_Players);
    }

    public function getPlaceLeft(): int
    {
        return $this->getMaxPlayer() - $this->getPlayerCount();
    }

    /**
     * @param null $m_Spawn
     */
    public function setSpawn($m_Spawn)
    {
        $this->m_Spawn = $m_Spawn;
    }

    /**
     * @param string $m_Name
     */
    public function setName(string $m_Name)
    {
        $this->m_Name = $m_Name;
        $this->updatePrefix();
    }

//    /**
//     * @param string $m_Prefix
//     */
//    public function setPrefix(string $m_Prefix)
//    {
//        $this->m_Prefix = $m_Prefix;
//    }

    public function updatePrefix()
    {
        $this->m_Prefix = TextFormat::RESET . ColorUtils::getTextFormatFromColor($this->getColor()) . "◀". ucfirst(substr($this->getName(), 0, 1)) ."▶" . TextFormat::WHITE . TextFormat::RESET;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->m_Name;
    }

    public function getColoredName(): string
    {
        return ColorUtils::getTextFormatFromColor($this->getColor()) . $this->getName() . TextFormat::WHITE . TextFormat::RESET;
    }

    public function getColor(): string
    {
        return $this->m_Color;
    }

    public function getPlayerCount():int
    {
        return count($this->m_Players);
    }

    /**
     * @return int
     */
    public function getMaxPlayer(): int
    {
        return $this->m_MaxPlayer;
    }

    /**
     * @param int $m_MaxPlayer
     */
    public function setMaxPlayer(int $m_MaxPlayer)
    {
        $this->m_MaxPlayer = $m_MaxPlayer;
    }

    /**
     * @param string $p_Color /!\ see ColorUtils constants
     */
    public function setColor(string $p_Color)
    {
        $this->m_Color = $p_Color;
        $this->updatePrefix();
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->m_Prefix;
    }

    /**
     * @return array
     */
    public function getOnlinePlayers(): array
    {
        $l_Players = [];

        foreach ($this->m_Players as $l_PlayerRawUUID) {
            $l_PlayerUUID = UUID::fromBinary($l_PlayerRawUUID);
            if ($l_PlayerUUID instanceof UUID) {
                $l_Player = PlayersManager::getInstance()->getPlayerFromUUID($l_PlayerUUID);
                if (!is_null($l_Player))
                    $l_Players[] = $l_Player;
            }
        }

        return $l_Players;
    }

    public function getPlayerUUIDs(): array
    {
        $l_PlayerUUIDs = [];

        foreach ($this->m_Players as $l_PlayerRawUUID)
            $l_PlayerUUIDs[] = UUID::fromBinary($l_PlayerRawUUID);

        return $l_PlayerUUIDs;
    }

    public function getSpawn(): ?Spawn
    {
        return $this->m_Spawn;
    }
}