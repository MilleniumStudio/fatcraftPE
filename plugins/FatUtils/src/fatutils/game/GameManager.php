<?php
/**
 * Created by PhpStorm.
 * User: Nyhven
 * Date: 07/09/2017
 * Time: 11:18
 */

namespace fatutils\game;


use fatutils\FatUtils;

class GameManager
{
    const GAME_STATE_WAITING = 0;
    const GAME_STATE_PLAYING = 1;

    const CONFIG_KEY_WAITING_SEC_DURATION = "waitingSecDuration";
    const CONFIG_KEY_PLAYING_SEC_DURATION = "playingSecDuration";

    private $m_State = GameManager::GAME_STATE_WAITING;
    private $m_WaitingTickDuration = 1800; // 30sec
    private $m_PlayingTickDuration = 6000; // 5min
    private static $m_Instance = null;

    public static function getInstance(): GameManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new GameManager();
        return self::$m_Instance;
    }

    /**
     * PlayersManager constructor.
     */
    private function __construct()
    {
        if (!is_null(FatUtils::getInstance()->getTemplateConfig()))
        {
            $this->setWaitingTickDuration(FatUtils::getInstance()->getTemplateConfig()->get(GameManager::CONFIG_KEY_WAITING_SEC_DURATION, 30) * 20);
            $this->setPlayingTickDuration(FatUtils::getInstance()->getTemplateConfig()->get(GameManager::CONFIG_KEY_PLAYING_SEC_DURATION, 5 * 60) * 20);
        }
    }

    /**
     * @return int
     */
    public function getWaitingTickDuration(): int
    {
        return $this->m_WaitingTickDuration;
    }

    /**
     * @param int $m_WaitingTickDuration
     */
    public function setWaitingTickDuration(int $m_WaitingTickDuration)
    {
        $this->m_WaitingTickDuration = $m_WaitingTickDuration;
    }

    /**
     * @return int
     */
    public function getPlayingTickDuration(): int
    {
        return $this->m_PlayingTickDuration;
    }

    /**
     * @param int $m_GameTickDuration
     */
    public function setPlayingTickDuration(int $m_GameTickDuration)
    {
        $this->m_PlayingTickDuration = $m_GameTickDuration;
    }

    public function setWaiting()
    {
        $this->m_State = GameManager::GAME_STATE_PLAYING;
    }

    public function setPlaying()
    {
        $this->m_State = GameManager::GAME_STATE_PLAYING;
    }

    public function isWaiting()
    {
        return $this->m_State === GameManager::GAME_STATE_WAITING;
    }

    public function isPlaying()
    {
        return $this->m_State === GameManager::GAME_STATE_PLAYING;
    }

}