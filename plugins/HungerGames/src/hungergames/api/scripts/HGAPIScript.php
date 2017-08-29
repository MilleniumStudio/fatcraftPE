<?php
namespace hungergames\api\scripts;
use hungergames\Loader;
use hungergames\obj\HungerGames;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;
abstract class HGAPIScript{
    /** @var string */
    public $scriptName;
    /** @var string */
    public $version;
    /** @var string */
    public $author;
    /** @var MainLogger */
    private $logger;
    /** @var bool */
    protected $enabled = true;
    /** @var string */
    private $scriptConfigPath;
    /** @var Config */
    protected $config;

    public function __construct($name, $version = "1.0", $author = "Author Name"){
        $this->scriptName = $name;
        $this->version = $version;
        $this->author = $author;
        $this->logger = MainLogger::getLogger();
        $this->scriptConfigPath = Loader::getInstance()->dataPath()."scriptConfigs/";
    }
    /**
     * Creates script config
     *
     * @param $name
     * @param array $values
     * @return Config
     */
    public function createConfig($name, array $values){
        if(substr($name, strlen($name)-4) !== ".yml") {
            $this->config = new Config($this->scriptConfigPath . $name . ".yml", Config::YAML, $values);
        }else{
            $this->config = new Config($this->scriptConfigPath . $name, Config::YAML, $values);
        }
        return $this->config;
    }
    /**
     * Gets script config
     *
     * @return Config
     */
    public function getConfig(){
        return $this->config;
    }
    /**
     * Gets the name of the script
     *
     * @return string
     */
    public function getName(){
        return $this->scriptName;
    }
    /**
     * Gets the name of the script
     *
     * @return string
     */
    public function getVersion(){
        return $this->scriptName;
    }
    /**
     * Gets the author of the script
     *
     * @return string
     */
    public function getAuthor(){
        return $this->author;
    }
    /**
     * disables script
     */
    public function setDisabled(){
        $this->enabled = false;
    }
    /**
     * enables script
     */
    public function setEnabled(){
        $this->enabled = true;
    }
    /**
     * returns whether script is enabled or not
     *
     * @return bool
     */
    public function isEnabled(){
        return $this->enabled;
    }
    /**
     * Sends console message
     *
     * @param $message
     */
    public function sendConsoleMessage($message){
        $this->logger->notice($message, "HungerGames Script: ".$this->getName());
    }
    /**
     * Called when script is loaded
     */
    public function onLoad(){
    }
    /**
     * called when player joins game
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function onPlayerJoinGame(Player $p, HungerGames $game){

    }
    /**
     * called when player quits game
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function onPlayerQuitGame(Player $p, HungerGames $game){

    }
    /**
     * Called when player fails to join full game
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function gameIsFull(Player $p, HungerGames $game){

    }

    /**
     * Called when player is waiting for players
     *
     * @param array $players
     * @param HungerGames $game
     */
    public function whileWaitingForPlayers(array $players, HungerGames $game){

    }
    /**
     * Called when player is waiting for players
     *
     * @param array $players
     * @param HungerGames $game
     */
    public function whileWaitingToStart(array $players, HungerGames $game){

    }
    /**
     * Called when game starts
     *
     * @param array $players
     * @param HungerGames $game
     */
    public function onGameStart(array $players, HungerGames $game){

    }
    /**
     * Called when death match starts
     *
     * @param array $players
     * @param HungerGames $game
     */
    public function onDeathMatchStart(array $players, HungerGames $game){

    }
    /**
     * Called when players wins a game
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function onPlayerWinGame(Player $p, HungerGames $game){

    }
}