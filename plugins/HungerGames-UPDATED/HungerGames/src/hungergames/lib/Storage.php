<?php
namespace hungergames\lib;
class Storage{
    /** @var array */
    protected $players = [];
    /** @var array */
    protected $waitingPlayers = [];
    /**
     * Clears out game players array
     */
    public function clearPlayers(){
        if($this->isPlayersCleared()) return;
        unset($this->players);
    }
    /**
     * Check if players is cleared
     *
     * @return bool
     */
    public function isPlayersCleared(){
        return $this->players === [];
    }
    /**
     * Clears out game waiting players array
     */
    public function clearWaitingPlayers(){
        if($this->isPlayersCleared()) return;
        unset($this->waitingPlayers);
    }
    /**
     * Check if waiting players is cleared
     *
     * @return bool
     */
    public function isWaitingPlayersCleared(){
        return $this->waitingPlayers === [];
    }
    /**
     * Clears out array index
     *
     * @param array $array
     * @param $index
     */
    public function clearOutIndexFromArray(array $array, $index){
        if(!isset($array[$index])) return;
        unset($array[$index]);
    }
    /**
     * Clears out multi array index
     *
     * @param array $array
     * @param $index
     */
    public function clearOutIndexFromMultiArray(array $array, $index){
        foreach($array as $res => $ret){
            foreach($ret as $off => $tar) {
                if($tar !== $index) continue;
                unset($array[$ret][$off]);
                return;
            }
        }
    }
}