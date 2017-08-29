<?php
namespace hungergames\obj;
use hungergames\lib\utils\exc;
use hungergames\Loader;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\utils\Config;
class HungerGames extends Game{
    /** @var int */
    public $min;
    /** @var int */
    public $max;
    /** @var float */
    public $gameSeconds;
    /** @var float */
    public $waitingSeconds;
    /** @var Level */
    public $gameLevel;
    /** @var Position */
    public $lobbyPos;
    /** @var Position */
    public $deathMatchPos;
    /** @var Position[] */
    private $slots;
    /** @var bool */
    private $isSkyWars;
    /** @var Item[] */
    private $chestItems;
    /** @var float */
    private $refillAfter;
    /** @var \string[] */
    private $signList;
    /** @var bool */
    private $init = false;
    /** @var Config */
    private $game;
    /**
     * Initiates game data
     */
    public function init(){
        $this->game = Loader::getInstance()->getGameArenaByName($this->getName());
        $game = $this->game;
        $this->min = exc::stringToInteger($game->get("min_players"));
        $this->max = exc::stringToInteger($game->get("max_players"));
        $this->gameSeconds = floatval($game->get("game_seconds"));
        $this->waitingSeconds = floatval($game->get("waiting_seconds"));
        Loader::getInstance()->getServer()->loadLevel($game->get("game_level"));
        $this->gameLevel = Loader::getInstance()->getServer()->getLevelByName($game->get("game_level"));
        $lobby_pos = $game->get("lobby_pos");
        Loader::getInstance()->getServer()->loadLevel($lobby_pos["level"]);
        $lobby_level = Loader::getInstance()->getServer()->getLevelByName($lobby_pos["level"]);
        $this->lobbyPos = new Position(floatval($lobby_pos["x"]), floatval($lobby_pos["y"]), floatval($lobby_pos["z"]), $lobby_level);
        $dm_pos = $game->get("death_match_pos");
        Loader::getInstance()->getServer()->loadLevel($dm_pos["level"]);
        $dm_level = Loader::getInstance()->getServer()->getLevelByName($dm_pos["level"]);
        $this->deathMatchPos = new Position(floatval($dm_pos["x"]), floatval($dm_pos["y"]), floatval($dm_pos["z"]), $dm_level);
        $this->slots = $game->get("slots");
        $this->isSkyWars = $game->get("is_sky_wars");
        $this->chestItems = $game->get("chest_items");
        $this->refillAfter = $game->get("refill_chests_after_seconds");
        $this->signList = $game->get("sign_list");
        $this->init = true;
    }
    /**
     * get the game configuration arena
     *
     * @return Config
     */
    public function getGameArena(){
        return $this->game;
    }
    /**
     * Checks if game is initiated
     *
     * @return bool
     */
    public function isHGInitiated(){
        return $this->init !== false;
    }
    /**
     * Minimum amount of players of game
     *
     * @return int
     */
    public function getMinimumPlayers(){
        return $this->min;
    }
    /**
     * Maximum amount of players of game
     *
     * @return int
     */
    public function getMaximumPlayers(){
        return $this->max;
    }
    /**
     * Game seconds of game
     *
     * @return float
     */
    public function getGameSeconds(){
        return $this->gameSeconds;
    }
    /**
     * Waiting seconds of game
     *
     * @return float
     */
    public function getWaitingSeconds(){
        return $this->waitingSeconds;
    }
    /**
     * Level of game
     *
     * @return Level
     */
    public function getGameLevel(){
        return $this->gameLevel;
    }
    /**
     * Position of game lobby
     *
     * @return Position
     */
    public function getLobbyPosition(){
        return $this->lobbyPos;
    }
    /**
     * Position of game death match
     *
     * @return Position
     */
    public function getDeathMatchPosition(){
        return $this->deathMatchPos;
    }
    /**
     * Returns all slots of games
     *
     * @return Position[]|null
     */
    public function getSlots(){
        $slots = [];
        foreach($this->slots as $slotNumber => $pos){
            $slots[] = new Position(floatval($pos["x"]), floatval($pos["y"]), floatval($pos["z"]), $this->gameLevel);
        }
        return $slots === null ? null : $slots;
    }
    /**
     * Returns if game is SkyWars
     *
     * @return bool
     */
    public function isSkyWars(){
        return strtolower($this->isSkyWars);
    }
    /**
     * Returns after how much time the chests are refilled
     *
     * @return float
     */
    public function refillAfter(){
        return $this->refillAfter;
    }
    /**
     * Returns all chest items
     *
     * @return Item[]
     */
    public function getChestItems(){
        $items = [];
        foreach($this->chestItems as $item){
            $item = explode(" ", $item);
            if(count($item) < 3) $items[] = Item::get(0, 0, 1);
            $rd = mt_rand(1, 2);
            if($rd > 1){
                $items[] = Item::get(0, 0, 1);
            }else{
                $items[] = Item::get($item[0], $item[1], $item[2]);
            }
        }
        return $items;
    }
    /**
     * Gets signs list
     *
     * @return \string[]
     */
    public function getSignList(){
        return $this->signList;
    }
}