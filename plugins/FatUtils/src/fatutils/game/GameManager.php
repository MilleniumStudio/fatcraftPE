<?php
/**
 * Created by PhpStorm.
 * User: Nyhven
 * Date: 07/09/2017
 * Time: 11:18
 */

namespace fatutils\game;


class GameManager
{
    const GAME_STATE_WAITING = 0;
    const GAME_STATE_PLAYING = 1;

    private $m_State = GAME_STATE_WAITING;
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
    private function __construct() {}

    public function setWaiting()
    {
        $this->m_State = GAME_STATE_PLAYING;
    }

    public function setPlaying()
    {
        $this->m_State = GAME_STATE_PLAYING;
    }

    public function isWaiting()
    {
        return $this->m_State === GAME_STATE_WAITING;
    }

    public function isPlaying()
    {
        return $this->m_State === GAME_STATE_PLAYING;
    }

}