<?php
namespace hungergames\obj;
use hungergames\lib\utils\exc;
use hungergames\Loader;
class Game{
    /** @var string */
    private $name = "";
    /** @var string */
    private $uniqueId;
    /** @var HungerGames */
    private $gameC;

    /** @var int */
    public static $game_amount = 0;

    /**
     * Loads game
     *
     * @param HungerGames $game
     */
    public function loadGame(HungerGames $game){
        $this->gameC = $game;
    }
    /**
     * Creates a Hunger Games game
     *
     * @param string $name
     */
    public function create($name = null){
        ++Game::$game_amount;
        if($name){
            $this->name .= $name;
        }else{
            $this->name .= "Game".(Game::$game_amount);
        }
        $this->uniqueId = md5(uniqid($this->name, true));
        Loader::getInstance()->createGameArena($this->gameC);
        Loader::getInstance()->createGameResource($this->name, $this->gameC);
        $this->gameC->init();
    }
    /**
     * Destroys game by itself
     *
     * @param bool $confirm
     */
    public function delete($confirm = false){
        if($confirm) {
            --Game::$game_amount;
            Loader::getInstance()->getStorage()->removePlayersInGame($this->gameC);
            Loader::getInstance()->getStorage()->removePlayersInWaitingGame($this->gameC);
            Loader::getInstance()->deleteGameArena($this->gameC);
            Loader::getInstance()->deleteGameResource($this->name);
            Loader::getInstance()->getLogger()->notice(exc::_("%%6The game {$this->getName()} has been deleted."));
            return;
        }
        Loader::getInstance()->getLogger()->notice(exc::_("%%4Attempted to delete game {$this->name} but has not been confirmed and did not delete game."));
    }
    /**
     * Get the game name
     *
     * @return string
     */
    public function getName(){
        return $this->name;
    }
    /**
     * Updates the game name
     *
     * @param $new
     */
    public function setName($new){
        $this->name = $new;
        Loader::getInstance()->updateResourceData($this->name, $this->gameC);
    }
    /**
     * Get the game id
     *
     * @return string
     */
    public function getUniqueId(){
        return $this->uniqueId;
    }
}