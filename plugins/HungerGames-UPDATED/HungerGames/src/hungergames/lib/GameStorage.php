<?php
namespace hungergames\lib;
use hungergames\lib\mgr\GameManager;
use pocketmine\Player;
use hungergames\Loader;
use hungergames\obj\HungerGames;
class GameStorage extends Storage{
    /** @var int */
    private $gameOverloadSize = 25;
    /** @var int */
    private $playerOverloadSize = 600;
    /**
     * Loads all games
     * @param HungerGames $game
     */
    public function loadGame(HungerGames $game){
        $this->players[$game->getName()] = [];
        $this->waitingPlayers[$game->getName()] = [];
    }
    /**
     * Searches for game in game list
     *
     * @param HungerGames $game
     * @return bool
     */
    public function searchGame(HungerGames $game){
        return isset($this->players[$game->getName()]);
    }
    /**
     * Add a game player
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function addPlayer(Player $p, HungerGames $game){
        if($this->searchGame($game)){
            $this->players[$game->getName()][] = $p;
        }
    }
    /**
     * Gets game of player playing a game
     *
     * @param Player $p
     * @return HungerGames|null
     */
    public function getPlayerGame(Player $p){
        foreach($this->players as $game => $players){
            foreach($players as $pl){
                if($pl === $p)
                    return Loader::getInstance()->getGlobalManager()->getGameByName($game);
            }
        }
        return null;
    }
    /**
     * Removes a game player
     *
     * @param Player $p
     */
    public function removePlayer(Player $p){
        foreach($this->players as $no => $game){
            foreach($game as $i => $j){
                if($j === $p){
                    unset($this->players[$no][$i]);
                }
            }
        }
    }
    /**
     * Removes all players
     */
    public function removeAllPlayers(){
        foreach($this->getAllPlayers() as $p){
            $this->removePlayer($p);
        }
    }
    /**
     * Returns all players in a game
     *
     * @param HungerGames $game
     * @return null|Player[]
     */
    public function getPlayersInGame(HungerGames $game){
        $players = [];
        if($this->searchGame($game)){
            foreach($this->players[$game->getName()] as $o => $p) {
                if ($p instanceof Player) {
                    $players[] = $p;
                }
            }
        }
        return $players !== null ? $players : null;
    }
    /**
     * Returns count of all players in a game
     *
     * @param HungerGames $game
     * @return int
     */
    public function getPlayersInGameCount(HungerGames $game){
        return count($this->getPlayersInGame($game));
    }
    /**
     * Checks if player is set in players
     *
     * @param Player $p
     * @return bool
     */
    public function isPlayerSet(Player $p){
        foreach($this->players as $n => $game){
            foreach($game as $i => $j){
                if($j === $p) return true;
            }
        }
        return false;
    }
    /**
     * Returns array of players in play
     *
     * @return Player[]
     */
    public function getAllPlayers(){
        $players = [];
        foreach($this->players as $res => $ret){
            foreach($ret as $sal => $tar){
                $players[] = $tar;
            }
        }
        return $players;
    }
    /**
     * Returns count of all players
     *
     * @return int
     */
    public function getAllPlayersCount(){
        return count($this->getAllPlayers());
    }
    /**
     * Removes all players from game
     *
     * @param HungerGames $game
     */
    public function removePlayersInGame(HungerGames $game){
        foreach($this->getPlayersInGame($game) as $p){
            $this->removePlayer($p);
        }
    }
    /**
     * Searches if there are game players waiting
     *
     * @param HungerGames $game
     * @return bool
     */
    public function searchAwaitingGame(HungerGames $game){
        return isset($this->waitingPlayers[$game->getName()]);
    }
    /**
     * Adds a waiting player to array
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function addWaitingPlayer(Player $p, HungerGames $game){
        if($this->searchAwaitingGame($game)){
            $this->waitingPlayers[$game->getName()][] = $p;
        }
    }
    /**
     * Gets game of waiting player playing a game
     *
     * @param Player $p
     * @return HungerGames|null
     */
    public function getWaitingPlayerGame(Player $p){
        foreach($this->waitingPlayers as $game => $players){
            foreach($players as $pl) {
                if($pl === $p)
                return Loader::getInstance()->getGlobalManager()->getGameByName($game);
            }
        }
        return null;
    }
    /**
     * Removes a waiting players from array
     *
     * @param Player $p
     */
    public function removeWaitingPlayer(Player $p){
        foreach($this->waitingPlayers as $no => $game){
            foreach($game as $i => $j){
                if($j === $p){
                    unset($this->waitingPlayers[$no][$i]);
                }
            }
        }
    }
    /**
     * Removes all waiting players
     */
    public function removeAllWaitingPlayers(){
        foreach($this->getAllWaitingPlayers() as $p){
            $this->removeWaitingPlayer($p);
        }
    }
    /**
     * Returns all players in a waiting game
     *
     * @param HungerGames $game
     * @return null|Player[]
     */
    public function getPlayersInWaitingGame(HungerGames $game){
        $players = [];
        if($this->searchGame($game)){
            foreach($this->waitingPlayers[$game->getName()] as $o => $p) {
                if ($p instanceof Player) {
                    $players[] = $p;
                }
            }
        }
        return $players !== null ? $players : null;
    }
    /**
     * Returns count of all waiting players in a game
     *
     * @param HungerGames $game
     * @return int
     */
    public function getAllWaitingPlayersInGameCount(HungerGames $game){
        return count($this->getPlayersInWaitingGame($game));
    }
    /**
     * Removes all waiting players from game
     *
     * @param HungerGames $game
     */
    public function removePlayersInWaitingGame(HungerGames $game){
        foreach($this->getPlayersInGame($game) as $p){
            $this->removeWaitingPlayer($p);
        }
    }
    /**
     * Returns array of players waiting
     *
     * @return Player[]
     */
    public function getAllWaitingPlayers(){
        $players = [];
        foreach($this->waitingPlayers as $res => $ret){
            foreach($ret as $sal => $tar){
                $players[] = $tar;
            }
        }
        return $players;
    }
    /**
     * Returns count of all waiting players
     *
     * @return int
     */
    public function getAllWaitingPlayersCount(){
        return count($this->getAllWaitingPlayers());
    }
    /**
     * Checks if player is set in waiting players
     *
     * @param Player $p
     * @return bool
     */
    public function isPlayerWaiting(Player $p){
        foreach($this->waitingPlayers as $n => $game){
            foreach($game as $i => $j){
                if($j === $p) return true;
            }
        }
        return false;
    }
    /**
     * Checks if game is overloaded
     *
     * @return bool
     */
    public function scanOverload(){
        $array_sum = (count($this->getAllPlayers()) + count($this->getAllWaitingPlayers()));
        if($array_sum >= $this->playerOverloadSize or GameManager::$runningGames >= $this->gameOverloadSize or
            ($array_sum >= $this->playerOverloadSize and GameManager::$runningGames >= $this->gameOverloadSize)){
            return true;
        }
        return false;
    }
}