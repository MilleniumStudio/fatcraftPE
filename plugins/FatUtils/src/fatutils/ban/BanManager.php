<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 10/10/2017
 * Time: 16:30
 */

namespace fatutils\ban;


use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use libasynql\result\MysqlResult;
use libasynql\result\MysqlSelectResult;
use libasynql\result\MysqlSuccessResult;
use pocketmine\Player;
use pocketmine\utils\UUID;

class BanManager
{
    private $m_UuidBans = [];
    private $m_IpBans = [];

    private static $m_Instance = null;

    public static function getInstance(): BanManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new BanManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        $this->init();
    }

    private function init()
    {
        // CREATING table if not exist
        LoadBalancer::getInstance()->connectMainThreadMysql()->query("
            CREATE TABLE `bans` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `player_uuid` varchar(36) DEFAULT NULL,
              `player_ip` varchar(15) DEFAULT NULL,
              `expiration_date` timestamp NULL DEFAULT NULL,
              `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_expiration_date` (`expiration_date`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
        ");

        // LOADING current bans
        $result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
            "SELECT * FROM bans WHERE expiration_date IS NULL OR expiration_date > NOW()", []);

        if ($result instanceof MysqlSelectResult)
        {
            foreach ($result->rows as $l_Row)
            {
                if (!is_null($l_Row["player_uuid"]))
                {
                    $l_Ban = Ban::createUuidBan($l_Row["id"], UUID::fromString($l_Row["player_uuid"]), intval($l_Row["expiration_date"]));
                    if (!is_null($l_Ban))
                        $this->m_UuidBans[$l_Ban->getUuid()->toString()] = $l_Ban;
                }
                else if (!is_null($l_Row["player_ip"]))
                {
                    $l_Ban = Ban::createIpBan($l_Row["id"], $l_Row["player_ip"], intval($l_Row["expiration_date"]));
                    if (!is_null($l_Ban))
                        $this->m_IpBans[$l_Ban->getIp()] = $l_Ban;
                }
            }
        }
    }

    public function banIp(string $p_Ip, int $p_ExpireSecondFromNow = null)
    {
        $l_Result = null;
        $l_ExpireTimestamp = (is_null($p_ExpireSecondFromNow) ? null : time() + $p_ExpireSecondFromNow);
        if (!is_null($p_ExpireSecondFromNow))
        {
            $l_Result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                "INSERT INTO bans (player_ip, expiration_date) VALUE (?, FROM_UNIXTIME(?));", [
                    ["s", $p_Ip],
                    ["i", time() + $p_ExpireSecondFromNow]
                ]);
        } else
        {
            $l_Result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                "INSERT INTO bans (player_ip) VALUE (?);", [
                    ["s", $p_Ip],
                ]);
        }

        if ($l_Result instanceof MysqlSuccessResult)
        {
            if ($l_Result->insertId > 0)
            {
                $this->m_IpBans[$p_Ip] = Ban::createIpBan($l_Result->insertId, $p_Ip, $l_ExpireTimestamp);
            }
        }
    }

    public function banUuid(UUID $p_Uuid, int $p_ExpireSecondFromNow = null)
    {
        $l_Result = null;
        $l_ExpireTimestamp = (is_null($p_ExpireSecondFromNow) ? null : time() + $p_ExpireSecondFromNow);
        if (!is_null($l_ExpireTimestamp))
        {
            $l_Result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                "INSERT INTO bans (player_uuid, expiration_date) VALUE (?, FROM_UNIXTIME(?));", [
                    ["s", $p_Uuid->toString()],
                    ["i", $l_ExpireTimestamp]
                ]);
        } else
        {
            $l_Result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                "INSERT INTO bans (player_uuid) VALUE (?);", [
                    ["s", $p_Uuid->toString()],
                ]);
        }

        if ($l_Result instanceof MysqlSuccessResult)
        {
            if ($l_Result->insertId > 0)
            {
                var_dump("BanCreate", $l_Result->insertId);
                $this->m_UuidBans[$p_Uuid->toString()] = Ban::createUuidBan($l_Result->insertId, $p_Uuid, $l_ExpireTimestamp);
            } else
                var_dump($l_Result);
        }
    }

    public function unbanIp(string $p_Ip):bool
    {
        if (array_key_exists($p_Ip, $this->m_IpBans))
        {
            $l_Ban = $this->m_IpBans[$p_Ip];
            if ($l_Ban instanceof Ban)
            {
                MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                    "UPDATE bans SET expiration_date = NOW() WHERE id = ?;", [
                        ["i", $l_Ban->getId()]
                    ]);
                unset($this->m_IpBans[$p_Ip]);
            }
            return true;
        }

        return false;
    }

    public function unbanUuid(UUID $p_Uuid):bool
    {
        $l_RawUuid = $p_Uuid->toString();
        if (array_key_exists($l_RawUuid, $this->m_UuidBans))
        {
            $l_Ban = $this->m_UuidBans[$l_RawUuid];
            if ($l_Ban instanceof Ban)
            {
                MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                    "UPDATE bans SET expiration_date = NOW() WHERE id = ?;", [
                        ["i", $l_Ban->getId()]
                    ]);
                unset($this->m_UuidBans[$l_RawUuid]);
            }
            return true;
        }

        return false;
    }

    public function getPlayerBan(Player $p_Player):Ban
    {
        if (array_key_exists($p_Player->getUniqueId()->toString(), $this->m_UuidBans))
            return $this->m_UuidBans[$p_Player->getUniqueId()->toString()];
        else if (array_key_exists($p_Player->getAddress(), $this->m_IpBans))
            return $this->m_IpBans[$p_Player->getAddress()];

        return null;
    }

    public function isBanned(Player $p_Player):bool
    {

        $l_Ban = null;
        if (array_key_exists($p_Player->getUniqueId()->toString(), $this->m_UuidBans))
            $l_Ban = $this->m_UuidBans[$p_Player->getUniqueId()->toString()];
        else if (array_key_exists($p_Player->getAddress(), $this->m_IpBans))
            $l_Ban = $this->m_IpBans[$p_Player->getAddress()];

        if ($l_Ban instanceof Ban)
        {
            if ($l_Ban->isStillValid())
                return true;
            else
            {
                if (array_key_exists($p_Player->getUniqueId()->toString(), $this->m_UuidBans))
                    unset($this->m_UuidBans[$p_Player->getUniqueId()->toString()]);
                else if (array_key_exists($p_Player->getAddress(), $this->m_IpBans))
                    unset($this->m_IpBans[$p_Player->getAddress()]);
            }
        }

        return false;
    }
}