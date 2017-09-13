<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 12/09/2017
 * Time: 17:27
 */

namespace fatutils\tools;


use fatutils\FatUtils;
use fatutils\tools\bossBarAPI\BossBar;

class BossbarTimer extends Timer
{
    private $m_BossBar = null;
    private $m_Title = "";

    private $m_Players = null;

    public function addPlayers(array $p_Players)
    {
        $this->m_Players = $p_Players;
    }

    public function setTitle(string $p_Title):BossbarTimer
    {
        $this->m_Title = $p_Title;
        if ($this->m_BossBar instanceof BossBar)
            $this->m_BossBar->setTitle($this->m_Title);

        return $this;
    }

    public function start(): Timer
    {
        if (is_null($this->m_Players))
            $this->m_Players = FatUtils::getInstance()->getServer()->getOnlinePlayers();
        return parent::start();
    }

    public function _onStart()
    {
        parent::_onStart();
        $this->m_BossBar = (new BossBar())
                ->addPlayers($this->m_Players)
                ->setTitle($this->m_Title);
    }

    public function _onTick()
    {
        parent::_onTick();
        if ($this->getTimeLeft() % 2)
        {
            if ($this->m_BossBar instanceof BossBar)
            {
                $timeFormat = gmdate("H:i:s", $this->getSecondLeft());

                $this->m_BossBar->setTitle($this->m_Title . ": " . $timeFormat);
                $this->m_BossBar->setRatio($this->getTimeSpentRatio());
            }
        }

//        if ($this->getTimeLeft() % 40)
//        {
//            if ($this->m_BossBar instanceof BossBar)
//            {
//                $type = BossEventPacket::TYPE_TEXTURE;//rand(0, 15);
//                $color = rand(0, 15);
//                echo "t: " . $type . " c: " . $color . "\n";
//                $this->m_BossBar->setType($type);
//                $this->m_BossBar->setColor($color);
//            }
//        }
    }

    public function _onStop()
    {
        if ($this->m_BossBar instanceof BossBar)
            $this->m_BossBar->remove();
        parent::_onStop();
    }
}