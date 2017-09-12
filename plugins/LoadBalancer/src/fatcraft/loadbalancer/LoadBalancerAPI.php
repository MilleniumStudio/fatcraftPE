<?php

namespace fatcraft\loadbalancer;

class LoadBalancer
{

    public function getMain()
    {
        return fatcraft\loadbalancer\LoadBalancer::getInstance();
    }

    public function getThisStatus()
    {
        return $this->getMain()->getStatus();
    }

    public function setThisStatus(String $p_Status)
    {
        $this->getMain()->setStatus($p_Status);
    }

    public function getServers(String $Type = "all", String $status = "all")
    {
        return $this->getMain()->getServerData($Type, $status);
    }

    public function getPlayerServer(String $p_Name)
    {
        return $this->getMain()->getPlayerServerUUID($p_Name);
    }

    public function transportPlayer(\pocketmine\Player $p_Player, String $p_Type = "lobby", Integer $p_Id = -1, String $p_Message = "")
    {
        $l_Server = $this->getMain()->m_Servers[$p_Type][$p_Id];
        $this->getMain()->transferPlayer($p_Player, $l_Server['ip'], $l_Server['port'], $p_Message);
    }
}
