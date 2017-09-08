<?php
namespace hungergames\lib\mgr;
use hungergames\lib\editor\GameEditor;
use hungergames\Loader;
use hungergames\obj\HungerGames;
class GlobalManager{
    /** @var HungerGames[] */
    private $games = [];
    /** @var GameManager[] */
    private $gamesMgr = [];
    /** @var GameEditor[] */
    private $gamesEditor = [];
    /** @var Loader */
    private $HGApi;
    public function __construct(Loader $main){
        $this->HGApi = $main;
    }
    /**
     * Loads game into global manager
     *
     * @param HungerGames $game
     */
    public function load(HungerGames $game){
        $game->init();
        $this->games[$game->getName()] = $game;
        $game->loadGame($game);
        $this->HGApi->getStorage()->loadGame($game);
        $this->gamesMgr[$game->getName()] = new GameManager($game, $this->HGApi);
        $this->gamesEditor[$game->getName()] = new GameEditor($game);
        $level_src = $this->HGApi->getServer()->getDataPath()."worlds/".$game->gameLevel->getFolderName();
        $level_dst = $this->HGApi->dataPath()."mapBackups/".$game->gameLevel->getFolderName();
        $this->HGApi->getMapBackup()->write($level_src, $level_dst);
    }
    /**
     * Checks if game exists
     *
     * @param $resource
     * @return bool
     */
    public function exists($resource){
        return isset($this->games[$resource]);
    }
    /**
     * Remove game from global manager
     *
     * @param HungerGames $game
     */
    public function remove(HungerGames $game){
        if(isset($this->games[$game->getName()])){
            unset($this->games[$game->getName()]);
        }
        if(isset($this->gamesMgr[$game->getName()])){
            unset($this->gamesMgr[$game->getName()]);
        }
        if(isset($this->gamesEditor[$game->getName()])){
            unset($this->gamesEditor[$game->getName()]);
        }
    }
    /**
     * Get game by name from global manager
     *
     * @param $resource
     * @return HungerGames|null
     */
    public function getGameByName($resource){
        if(isset($this->games[$resource])){
            return $this->games[$resource];
        }
        return null;
    }
    /**
     * Gets game manager by hg object from global manager
     *
     * @param HungerGames $game
     * @return GameManager|null
     */
    public function getGameManager(HungerGames $game){
        if(isset($this->gamesMgr[$game->getName()])){
            return $this->gamesMgr[$game->getName()];
        }
        return null;
    }
    /**
     * Get game manager by name from global manager
     *
     * @param $resource
     * @return GameManager|null
     */
    public function getGameManagerByName($resource){
        if(isset($this->gamesMgr[$resource])){
            return $this->gamesMgr[$resource];
        }
        return null;
    }
    /**
     * Gets game editor by hg object from global manager
     *
     * @param HungerGames $game
     * @return null
     */
    public function getGameEditor(HungerGames $game){
        if(isset($this->gamesEditor[$game->getName()])){
            return $this->gamesEditor[$game->getName()];
        }
        return null;
    }
    /**
     * Get game manager by name from global manager
     *
     * @param $resource
     * @return GameEditor|null
     */
    public function getGameEditorByName($resource){
        if(isset($this->gamesEditor[$resource])){
            return $this->gamesEditor[$resource];
        }
        return null;
    }
    /**
     * Gets all loaded games
     *
     * @return \hungergames\obj\HungerGames[]
     */
    public function getGames(){
        return $this->games;
    }
    /**
     * Gets all game managers
     *
     * @return GameManager[]
     */
    public function getGameManagers(){
        return $this->gamesMgr;
    }
    /**
     * Gets all game editors
     *
     * @return \hungergames\lib\editor\GameEditor[]
     */
    public function getGameEditors(){
        return $this->gamesEditor;
    }
}