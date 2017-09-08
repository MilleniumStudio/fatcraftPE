<?php
namespace hungergames\lib\mgr;
use hungergames\lib\utils\Msg;
use hungergames\Loader;
use hungergames\obj\HungerGames;
use pocketmine\Player;
class GameManager{
    /** @var int */
    public static $runningGames = 0;
    /** @var string */
    public $status;
    /** @var HungerGames */
    public $game;
    /** @var Loader */
    private $HGApi;
    /** @var int */
    private $slotN;
    /** @var int */
    private $lastSlotN;

    public function __construct(HungerGames $game, Loader $main){
        $this->HGApi = $main;
        $this->game = $game;
        $this->status = "open";
        $this->slotN = $this->getOpenSlots();
    }

    /**
     * Gets loaded game
     *
     * @return HungerGames
     */
    public function getGame(){
        return $this->game;
    }

    /**
     * Refreshes game to default
     */
    public function refresh(){
        $this->status = "open";
        $this->slotN = $this->getOpenSlots();
        $this->lastSlotN = null;
        $this->isWaiting = false;
    }

    /**
     * Gets the status of the game
     *
     * @return string
     */
    public function getStatus(){
        return $this->status;
    }

    /**
     * Sets the status of the game
     *
     * @param $new
     */
    public function setStatus($new){
        $this->status = $new;
    }

    /**
     * Checks how much opens slots there are
     *
     * @return int
     */
    public function getOpenSlots(){
        return count($this->game->getSlots())-1;
    }

    /**
     * Teleport player to game position
     *
     * @param Player $p
     * @return bool
     */
    public function tpPlayerToOpenSlot(Player $p){
        if($this->slotN < 0) return false;
        $this->lastSlotN = $this->slotN;
        $slot = $this->getGame()->getSlots()[$this->slotN];
        $p->teleport($slot);
        $this->slotN -= 1;
        return true;
    }

    /**
     * Gets last used slot
     *
     * @return int
     */
    public function getLastUsedSlot(){
        return $this->lastSlotN;
    }

    /**
     * Sends game players message
     *
     * @param $message
     */
    public function sendGameMessage($message){
        $pig = $this->HGApi->getStorage()->getPlayersInGame($this->getGame());
        for($i = 0; $i < count($pig); ++$i){
            $pig[$i]->sendMessage($message);
        }
        $piWg = $this->HGApi->getStorage()->getPlayersInWaitingGame($this->getGame());
        for($i = 0; $i < count($piWg); ++$i){
            $piWg[$i]->sendMessage($message);
        }
    }

    /**
     * Sends game players tip
     *
     * @param $message
     */
    public function sendGameTip($message){
        $pig = $this->HGApi->getStorage()->getPlayersInGame($this->getGame());
        for($i = 0; $i < count($pig); ++$i){
            $pig[$i]->sendTip($message);
        }
        $piWg = $this->HGApi->getStorage()->getPlayersInWaitingGame($this->getGame());
        for($i = 0; $i < count($piWg); ++$i){
            $piWg[$i]->sendTip($message);
        }
    }

    /**
     * Sends game players popup
     *
     * @param $message
     */
    public function sendGamePopup($message){
        $pig = $this->HGApi->getStorage()->getPlayersInGame($this->getGame());
        for($i = 0; $i < count($pig); ++$i){
            $pig[$i]->sendPopup($message);
        }
        $piWg = $this->HGApi->getStorage()->getPlayersInWaitingGame($this->getGame());
        for($i = 0; $i < count($piWg); ++$i){
            $piWg[$i]->sendPopup($message);
        }
    }

    /**
     * Adds player into game
     *
     * @param Player $p
     * @param bool $message
     */
    public function addPlayer(Player $p, $message = false){
        $this->HGApi->getStorage()->addPlayer($p, $this->getGame());
        if(!$this->tpPlayerToOpenSlot($p)){
            foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                if(!$script->isEnabled()) continue;
                $script->gameIsFull($p, $this->getGame());
            }
            if($message){
                $p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.full"))));
            }
            return;
        }
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerJoinGame($p, $this->getGame());
        }
        if($message) {
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.join"))));
        }
    }

    /**
     * Removes player from game
     *
     * @param Player $p
     * @param bool $message
     */
    public function removePlayer(Player $p, $message = false){
        $this->HGApi->getStorage()->removePlayer($p);
        $p->teleport($this->getGame()->getLobbyPosition());
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerQuitGame($p, $this->getGame());
        }
        if($message){
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.quit"))));
        }
    }
    
        /**
     * Removes player from game without teleporting
     *
     * @param Player $p
     * @param bool $message
     */
    public function removePlayerWithoutTeleport(Player $p, $message = false){
        $this->HGApi->getStorage()->removePlayer($p);
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerQuitGame($p, $this->getGame());
        }
        if($message){
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.quit"))));
        }
    }
    /**
     * Adds player into game
     *
     * @param array $players
     * @param bool $message
     */
    public function addPlayers(array $players, $message = false){
        foreach($players as $p){
            if($p instanceof Player){
                if(!$this->tpPlayerToOpenSlot($p)){
                    foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                        if(!$script->isEnabled()) continue;
                        $script->gameIsFull($p, $this->getGame());
                    }
                    if($message){
                        $p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.full"))));
                    }
                    return;
                }
                $this->HGApi->getStorage()->addPlayer($p, $this->getGame());
                foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                    if(!$script->isEnabled()) continue;
                    $script->onPlayerJoinGame($p, $this->getGame());
                }
                if($message) {
                    $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.join"))));
                }
            }
        }
    }

    /**
     * Sets players of game
     *
     * @param array $players
     * @param bool $message
     */
    public function setPlayers(array $players, $message = false){
        foreach($this->HGApi->getStorage()->getPlayersInGame($this->getGame()) as $p) {
            $this->HGApi->getStorage()->removePlayer($p);
            $p->teleport($this->getGame()->getLobbyPosition());
            foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                if(!$script->isEnabled()) continue;
                $script->onPlayerQuitGame($p, $this->getGame());
            }
            if($message){
                $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.quit"))));
            }
        }
        foreach($players as $p){
            if($p instanceof Player) {
                if(!$this->tpPlayerToOpenSlot($p)){
                    foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                        if(!$script->isEnabled()) continue;
                        $script->gameIsFull($p, $this->getGame());
                    }
                    if($message){
                        $p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.full"))));
                    }
                    return;
                }
                $this->HGApi->getStorage()->addPlayer($p, $this->getGame());
                foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                    if(!$script->isEnabled()) continue;
                    $script->onPlayerJoinGame($p, $this->getGame());
                }
                if($message){
                    $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.join"))));
                }
            }
        }
    }
    /**
     * Replaces all waiting players
     *
     * @param Player $newPlayer
     * @param Player $oldPlayer
     * @param bool $message
     */
    public function replacePlayer(Player $newPlayer, Player $oldPlayer, $message = false){
        if(!$this->tpPlayerToOpenSlot($newPlayer)){
            foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                if(!$script->isEnabled()) continue;
                $script->gameIsFull($newPlayer, $this->getGame());
            }
            if($message){
                $newPlayer->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$newPlayer->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.full"))));
            }
            return;
        }
        $this->HGApi->getStorage()->addPlayer($newPlayer, $this->getGame());
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerJoinGame($newPlayer, $this->getGame());
        }
        if($message){
            $this->sendGameMessage(str_replace(["%player%", "%game%"], [$newPlayer->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.join")));
        }
        $this->HGApi->getStorage()->removePlayer($oldPlayer);
        $oldPlayer->teleport($this->getGame()->getLobbyPosition());
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerQuitGame($oldPlayer, $this->getGame());
        }
        if($message) {
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$oldPlayer->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.quit"))));
        }
    }
    /**
     * Adds player into game
     *
     * @param Player $p
     * @param bool $message
     */
    public function addWaitingPlayer(Player $p, $message = false){
        if(!$this->tpPlayerToOpenSlot($p)){
            foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                if(!$script->isEnabled()) continue;
                $script->gameIsFull($p, $this->getGame());
            }
            if($message){
                $p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.full"))));
            }
            return;
        }
        $this->HGApi->getStorage()->addWaitingPlayer($p, $this->getGame());
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerJoinGame($p, $this->getGame());
        }
        if($message){
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"],[$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.join"))));
        }
    }
    /**
     * Removes player from game
     *
     * @param Player $p
     * @param bool $message
     */
    public function removeWaitingPlayer(Player $p, $message = false){
        $p->teleport($this->getGame()->getLobbyPosition());
        $this->HGApi->getStorage()->removeWaitingPlayer($p);
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerJoinGame($p, $this->getGame());
        }
        if($message) {
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.quit"))));
        }
    }
    /**
     * removes all waiting players
     *
     * @param bool|false $message
     */
    public function removeWaitingPlayers($message = false){
        foreach($this->HGApi->getStorage()->getAllWaitingPlayers() as $p){
            $p->teleport($this->getGame()->getLobbyPosition());
            $this->HGApi->getStorage()->removeWaitingPlayer($p);
            foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                if(!$script->isEnabled()) continue;
                $script->onPlayerQuitGame($p, $this->getGame());
            }
            if($message) {
                $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.quit"))));
            }
        }
    }
    /**
     * Adds waiting player into game
     *
     * @param array $players
     * @param bool $message
     */
    public function addWaitingPlayers(array $players, $message = false){
        foreach($players as $p) {
            if ($p instanceof Player) {
                $this->HGApi->getStorage()->addWaitingPlayer($p, $this->getGame());
                if(!$this->tpPlayerToOpenSlot($p)){
                    foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                        if(!$script->isEnabled()) continue;
                        $script->gameIsFull($p, $this->getGame());
                    }
                    if($message){
                        $p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.full"))));
                    }
                    return;
                }
                foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                    if(!$script->isEnabled()) continue;
                    $script->onPlayerJoinGame($p, $this->getGame());
                }
            }
            if ($message) {
                $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.join"))));
            }
        }
    }
    /**
     * Sets waiting players of game
     *
     * @param array $players
     * @param bool $message
     */
    public function setWaitingPlayers(array $players, $message = false){
        foreach($this->HGApi->getStorage()->getPlayersInWaitingGame($this->getGame()) as $p) {
            $p->teleport($this->getGame()->getLobbyPosition());
            $this->HGApi->getStorage()->removeWaitingPlayer($p);
            foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                if(!$script->isEnabled()) continue;
                $script->onPlayerQuitGame($p, $this->getGame());
            }
            if($message) {
                $p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.quit"))));
            }
        }
        foreach($players as $p){
            if($p instanceof Player) {
                if ($this->tpPlayerToOpenSlot($p)) {
                    $this->HGApi->getStorage()->addWaitingPlayer($p, $this->getGame());
                    foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                        if(!$script->isEnabled()) continue;
                        $script->onPlayerJoinGame($p, $this->getGame());
                    }
                    if($message) {
                        $p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.join"))));
                    }
                } else {
                    foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                        if(!$script->isEnabled()) continue;
                        $script->gameIsFull($p, $this->getGame());
                    }
                    if($message) {
                        $p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.full"))));
                    }
                }
            }
        }
    }

    /**
     * Swaps players
     *
     * @param Player $newPlayer
     * @param Player $oldPlayer
     * @param bool $message
     */
    public function replaceWaitingPlayer(Player $newPlayer, Player $oldPlayer, $message = false){
        $oldPlayer->teleport($this->getGame()->getLobbyPosition());
        $this->HGApi->getStorage()->removeWaitingPlayer($oldPlayer);
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerQuitGame($oldPlayer, $this->getGame());
        }
        if(!$this->tpPlayerToOpenSlot($newPlayer)){
            foreach($this->HGApi->getScriptManager()->getScripts() as $script){
                if(!$script->isEnabled()) continue;
                $script->gameIsFull($newPlayer, $this->getGame());
            }
            if($message){
                $newPlayer->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$newPlayer->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.full"))));
            }
            return;
        }
        $this->HGApi->getStorage()->addWaitingPlayer($newPlayer, $this->getGame());
        foreach($this->HGApi->getScriptManager()->getScripts() as $script){
            if(!$script->isEnabled()) continue;
            $script->onPlayerJoinGame($newPlayer, $this->getGame());
        }
        if($message){
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$oldPlayer->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.quit"))));
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$newPlayer->getName(), $this->getGame()->getName()], Msg::getHGMessage("hg.message.join"))));
        }
    }
    /** @var bool */
    public $isWaiting = false;
}
