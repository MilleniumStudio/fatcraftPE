<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 10/10/2017
 * Time: 18:20
 */

namespace fatutils\ban;


use fatcraft\loadbalancer\LoadBalancer;
use libasynql\result\MysqlResult;
use pocketmine\utils\UUID;

class Ban
{
    private $m_Id;
    private $m_PlayerUUID = null;
    private $m_PlayerIp = null;
    private $m_ExpirationTimestamp = null;

    private function __construct()
    {
    }

    public static function createUuidBan(int $p_Id, UUID $p_UUID, int $p_ExpireOnTimestamp = null):Ban
    {
        $l_Ret = new Ban();
        $l_Ret->m_Id = $p_Id;
        $l_Ret->m_PlayerUUID = $p_UUID;

        if ($p_ExpireOnTimestamp != 0)
            $l_Ret->m_ExpirationTimestamp = $p_ExpireOnTimestamp;

        return $l_Ret;
    }

    public static function createIpBan(int $p_Id, string $p_Ip, int $p_ExpireOnTimestamp = null):Ban
    {
        $l_Ret = new Ban();
        $l_Ret->m_Id = $p_Id;
        $l_Ret->m_PlayerIp = $p_Ip;

        if ($p_ExpireOnTimestamp != 0)
            $l_Ret->m_ExpirationTimestamp = $p_ExpireOnTimestamp;

        return $l_Ret;
    }

    public function isStillValid(): bool
    {
        var_dump($this, time());
        return is_null($this->m_ExpirationTimestamp) || $this->m_ExpirationTimestamp > time();
    }

    /**
     * @return mixed
     */
    public function getId():int
    {
        return $this->m_Id;
    }

    public function getUuid():UUID
    {
        return $this->m_PlayerUUID;
    }

    public function getIp():string
    {
        return $this->m_PlayerIp;
    }

    public function getExpirationTimestamp(): ?int
    {
        return $this->m_ExpirationTimestamp;
    }
}