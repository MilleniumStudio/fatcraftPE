<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 12/09/2017
 * Time: 17:27
 */

namespace fatutils\tools;

use fatutils\FatUtils;
use pocketmine\Player;

class TipsTimer extends Timer
{
    private $m_Title = "";

    private $m_Players = null;

    public function addPlayers(array $p_Players)
    {
        $this->m_Players = $p_Players;
    }

    /**
     * @param string|TextFormatter $p_Title
     * @return TipsTimer
     */
    public function setTitle($p_Title):TipsTimer
    {
        $this->m_Title = $p_Title;
        return $this;
    }

    public function _onStart()
    {
        parent::_onStart();
        $this->display();
    }


    public function _onTick()
    {
        parent::_onTick();

        if ($this->getTickLeft() % 20 == 0)
        {
            $l_SecondLeft = $this->getSecondLeft();
            if (($l_SecondLeft >= 1200 && $l_SecondLeft % 300 == 0) || // > 20 min: display every 5 min
                ($l_SecondLeft >= 300 && $l_SecondLeft % 60 == 0) || // > 5 min: display every 1 min
                ($l_SecondLeft >= 60 && $l_SecondLeft % 30 == 0) || // > 1 min: display every 30 sec
                ($l_SecondLeft >= 10 && $l_SecondLeft % 10 == 0) || // > 10 sec: display every 10 sec
                $l_SecondLeft < 10)// else: display every 1 sec
            {
                $this->display();
            }
        }
    }

    private function getPlayers():array
    {
        return (is_null($this->m_Players) ? FatUtils::getInstance()->getServer()->getOnlinePlayers() : $this->m_Players);
    }

    public function display(): void
    {
        $timeFormat = gmdate("H:i:s", $this->getSecondLeft());
        if ($this->m_Title instanceof TextFormatter)
            $this->m_Title->addParam("time", $timeFormat); //ref to param "{time}" in translation lines

        foreach ($this->getPlayers() as $l_Player)
        {
            if ($l_Player instanceof Player)
            {
                if ($this->m_Title instanceof TextFormatter)
                    $l_Text = $this->m_Title->asStringForPlayer($l_Player);
                else
                    $l_Text = $this->m_Title . ": " . $timeFormat;

//                $l_Player->sendMessage($l_Text);
                $l_Player->sendTip($l_Text);
            }
        }
    }
}