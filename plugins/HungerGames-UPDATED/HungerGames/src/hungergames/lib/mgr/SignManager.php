<?php
namespace hungergames\lib\mgr;
use hungergames\lib\utils\Msg;
use hungergames\Loader;
use hungergames\obj\HungerGames;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;

class SignManager{
    /** @var Loader */
    private $HGApi;
    /** @var int */
    private $refreshedSignsCount = 0;
    /** @var Sign[] */
    private $refreshedSigns = [];
    public function __construct(Loader $main){
        $this->HGApi = $main;
    }
    /**
     * Gets amount of refreshed signs
     *
     * @return int
     */
    public function getRefreshedSignCount(){
        return $this->refreshedSignsCount;
    }
    /**
     * Sets amount of refreshed signs
     *
     * @param $amount
     */
    public function setRefreshedSignsCount($amount){
        if(!is_int($amount)) return;
        $this->refreshedSigns = $amount;
    }
    /**
     * Gets refreshed signs tiles
     *
     * @return \pocketmine\tile\Sign[]
     */
    public function getRefreshedSigns(){
        return $this->refreshedSigns;
    }
    /**
     * clears refreshed signs cache
     */
    public function clearRefreshedSigns(){
        $this->refreshedSigns = [];
    }
    /**
     * Checks if a sign is a game sign
     *
     * @param Sign $tile
     * @return bool
     */
    public function isGameSign(Sign $tile){
        foreach($this->HGApi->getAllGameArenas() as $arena){
            $val = "{$tile->x}:{$tile->y}:{$tile->z}:{$tile->level->getName()}";
            if(isset($arena->getAll()["sign_list"][$val])) return true;
        }
        return false;
    }

    /**
     * Gets game sign
     *
     * @param Sign $tile
     * @return HungerGames|null
     */
    public function getSignGame(Sign $tile){
        foreach($this->HGApi->getGlobalManager()->getGames() as $game){
            $cf = (new Config($this->HGApi->dataPath()."arenas/{$game->getName()}.yml", Config::YAML))->getAll();
            $val = "{$tile->x}:{$tile->y}:{$tile->z}:{$tile->level->getName()}";
            if(isset($cf["sign_list"][$val])){
                return $game;
            }
        }
        return null;
    }

    /**
     * Refreshes all game signs
     */
    public function refreshAllSigns(){
        foreach($this->HGApi->getGlobalManager()->getGames() as $game){
            $this->refreshSigns($game);
        }
    }
    /**
     * @param HungerGames $game
     */
    public function refreshSigns(HungerGames $game){
        foreach($game->getSignList() as $tile => $nothingXD){
            $tile = explode(":", $tile);
            if(count($tile) < 4) continue;
            if(!$this->HGApi->getServer()->isLevelLoaded($tile[3])) continue;
            $tile = $this->HGApi->getServer()->getLevelByName($tile[3])->getTile(new Vector3((int)$tile[0], (int)$tile[1], (int)$tile[2]));
            if(!$tile instanceof Sign) continue;
            $lines = [];
            $lines[0] = $game->getGameArena()->get("sign_line_1");
            $lines[1] = $game->getGameArena()->get("sign_line_2");
            $lines[2] = $game->getGameArena()->get("sign_line_3");
            $lines[3] = $game->getGameArena()->get("sign_line_4");
            $outPut = [];
            foreach($lines as $line) {
                $on = $this->HGApi->getStorage()->getPlayersInGameCount($game) + $this->HGApi->getStorage()->getAllWaitingPlayersInGameCount($game);
                $max = $game->getMaximumPlayers();
                $gameName = $game->getName();
                $status = $this->HGApi->getGlobalManager()->getGameManager($game)->getStatus();
                $outPut[] = str_replace(
                    [
                        "{on}",
                        "{max}",
                        "{game}",
                        "{status}"
                    ],
                    [
                        $on,
                        $max,
                        $gameName,
                        $status
                    ], $line);
            }
            $tile->setText(Msg::color($outPut[0]), Msg::color($outPut[1]), Msg::color($outPut[2]), Msg::color($outPut[3]));
            ++$this->refreshedSignsCount;
            $this->refreshedSigns[] = $tile;
        }
    }
}