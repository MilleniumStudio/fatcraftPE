<?php
namespace hungergames;
use hungergames\api\scripts\HGAPIScriptManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use hungergames\hgmap\MapBackup;
use hungergames\lib\GameStorage;
use hungergames\lib\mgr\GlobalManager;
use hungergames\lib\mgr\SignManager;
use hungergames\tasks\LoadGamesTask;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use hungergames\obj\HungerGames;
use hungergames\lib\utils\Msg;
use hungergames\tasks\RefreshSignsTask;
class Loader extends PluginBase{
    /** @var Loader */
    private static $instance = null;

    /** @var GameStorage */
    private $storage;
    /** @var GlobalManager */
    private $globalManager;
    /** @var Config */
    private $messages;
    /** @var HGAPIScriptManager */
    private $scriptManager;
    /** @var MapBackup */
    private $mapBackup;
    /** @var SignManager */
    private $signManager;

    public function onLoad(){
        while(!self::$instance instanceof $this) {
            self::$instance = $this;
        }
    }

    public function onEnable(){
        $this->storage = new GameStorage();
        $this->globalManager = new GlobalManager($this);
        $this->scriptManager = new HGAPIScriptManager($this);
        $this->mapBackup = new MapBackup();
        $this->signManager = new SignManager($this);
        
	$messagesfile = $this->dataPath() . "messages.yml";
	if(!file_exists($messagesfile)) {
		file_put_contents($messagesfile, $this->getResource("messages.yml"));
	}
	$this->messages = new Config($this->dataPath()."messages.yml", Config::YAML, Msg::getDefaultHGMessages());
        //$this->getServer()->getCommandMap()->register("hg", new HGCommand($this));
        $this->getServer()->getScheduler()->scheduleDelayedTask(new LoadGamesTask($this), 20*5);
        $h = $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask($t = new RefreshSignsTask($this), 20*5, 20);
        $t->setHandler($h);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        @mkdir($this->dataPath());
        @mkdir($this->dataPath()."arenas/");
        @mkdir($this->dataPath()."resources/");
        @mkdir($this->dataPath()."scripts/");
        @mkdir($this->dataPath()."scriptConfigs/");
        @mkdir($this->dataPath()."mapBackups/");
        $this->scriptManager->loadScripts();
    }

    public function onCommand(CommandSender $sender, Command $command, string $commandLabel, array $args): bool {
        if(!$sender instanceof Player){
            $sender->sendMessage(Msg::color("&aPlease run this command in-game."));
            return false;
        }
        if(empty($args[0])){
            $sender->sendMessage(Msg::color("&a- /hg help"));
            return false;
        }
        switch(strtolower($args[0])){
            case "help":
                $sender->sendMessage(Msg::color("&aHungerGames Command"));
                $sender->sendMessage(Msg::color("&a- /hg add <game>"));
                $sender->sendMessage(Msg::color("&a- /hg del <game>"));
                $sender->sendMessage(Msg::color("&a- /hg min <game> <number>"));
                $sender->sendMessage(Msg::color("&a- /hg max <game> <number>"));
                $sender->sendMessage(Msg::color("&a- /hg level <game> <level name>"));
                $sender->sendMessage(Msg::color("&a- /hg ws <game> <seconds>"));
                $sender->sendMessage(Msg::color("&a- /hg gs <game> <seconds>"));
                $sender->sendMessage(Msg::color("&a- /hg lobby <game>"));
                $sender->sendMessage(Msg::color("&a- /hg dm <game>"));
                $sender->sendMessage(Msg::color("&a- /hg addslot <game> <name>"));
                $sender->sendMessage(Msg::color("&a- /hg delslot <game> <name>"));
                $sender->sendMessage(Msg::color("&a- /hg leave"));
            break;
            case "add":
                if(!$sender->hasPermission("hg.command.add"))
					return false;
                if(empty($args[1])){
                    $sender->sendMessage(Msg::color("&a- /hg add <game>"));
                    return false;
                }
                $game = $args[1];
                if($this->gameResourceExists($game) or $this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame already exists!"));
					return false;
                }
                $game1 = new HungerGames();
                $sender->sendMessage(Msg::color("&aCreating game $game... Please wait..."));
                $game1->loadGame($game1);
                $game1->create($game);
                $sender->sendMessage(Msg::color("&aSuccessfully created game $game!"));
            break;
            case "del":
                if(!$sender->hasPermission("hg.command.del"))
					return false;
                if(empty($args[1])){
                    $sender->sendMessage(Msg::color("&a- /hg del <game>"));
					return false;
                }
                $game = $args[1];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                if(empty($args[2])){
                    $sender->sendMessage(Msg::color("&cAre you sure you want to delete $game? &4&lYOU CAN NOT GET IT BACK!!"));
                    $sender->sendMessage(Msg::color("&aIf you are sure please run: /hg del $game proceed"));
					return false;
                }
                if(strtolower($args[2]) !== "proceed"){
                    $sender->sendMessage(Msg::color("&aDid you mean \"/hg del $game\"?"));
					return false;
                }
                $game1 = $this->getGameResource($game);
                $game1->delete(true);
                $sender->sendMessage(Msg::color("&cGame $game has been deleted! You can not get it back!"));
            break;
            case "min":
                if(!$sender->hasPermission("hg.command.min"))
					return false;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg min <game> <number>"));
					return false;
                }
                $game = $args[1];
                $number = $args[2];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                if(!is_numeric($number)){
                    $sender->sendMessage(Msg::color("&cInvalid int/number value."));
					return false;
                }
                $game1 = $this->getGlobalManager()->getGameEditorByName($game);
                $game1->setMinimumPlayers($number);
                $sender->sendMessage(Msg::color("&cMinimum players of game $game have been set to $number."));
            break;
            case "max":
                if(!$sender->hasPermission("hg.command.max"))
					return false;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg max <game> <number>"));
					return false;
                }
                $game = $args[1];
                $number = $args[2];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                if(!is_numeric($number)){
                    $sender->sendMessage(Msg::color("&cInvalid int/number value."));
					return false;
                }
                $game1 = $this->getGlobalManager()->getGameEditorByName($game);
                $game1->setMaximumPlayers($number);
                $sender->sendMessage(Msg::color("&aMaximum players of game $game have been set to $number."));
            break;
            case "level":
                if(!$sender->hasPermission("hg.command.level"))
					return false;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg level <game> <level name>"));
					return false;
                }
                $game = $args[1];
                $level = $args[2];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                $loaded = $this->getServer()->isLevelLoaded($this->getServer()->getLevelByName($level));
                $check = $this->getServer()->loadLevel($level);
                if(!$loaded){
                    if($check){
                        $game1 = $this->getGlobalManager()->getGameEditorByName($game);
                        $game1->setGameLevel($level);
                        $sender->sendMessage(Msg::color("&aSet game level of $game to $level."));
						return false;
                    }else{
                        $sender->sendMessage(Msg::color("&cCould not find any level with name $level."));
						return false;
                    }
                }
            break;
            case "ws":
                if(!$sender->hasPermission("hg.command.ws"))
					return false;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg ws <game> <seconds>"));
					return false;
                }
                $game = $args[1];
                $seconds = $args[2];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                if(!is_numeric($seconds)){
                    $sender->sendMessage(Msg::color("&cInvalid int/number value."));
					return false;
                }
                $game1 = $this->getGlobalManager()->getGameEditorByName($game);
                $game1->setWaitingSeconds($seconds);
                $sender->sendMessage(Msg::color("&aSet waiting seconds of game $game to $seconds."));
            break;
            case "gs":
                if(!$sender->hasPermission("hg.command.ws"))
					return false;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg gs <game> <seconds>"));
					return false;
                }
                $game = $args[1];
                $seconds = $args[2];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                if(!is_numeric($seconds)){
                    $sender->sendMessage(Msg::color("&cInvalid int/number value."));
					return false;
                }
                $game1 = $this->getGlobalManager()->getGameEditorByName($game);
                $game1->setWaitingSeconds($seconds);
                $sender->sendMessage(Msg::color("&aSet game seconds of game $game to $seconds."));
            break;
            case "addslot":
                if(!$sender->hasPermission("hg.command.slot.add"))
					return false;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg addslot <game> <name>"));
					return false;
                }
                $game = $args[1];
                $slot = $args[2];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                $game1 = $this->getGlobalManager()->getGameEditorByName($game);
                $game1->addSlot($sender, $slot);
                $sender->sendMessage(Msg::color("&aAdded slot $slot for game $game."));
            break;
            case "delslot":
                if(!$sender->hasPermission("hg.command.slot.del"))
					return false;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg delslot <game> <name>"));
					return false;
                }
                $game = $args[1];
                $slot = $args[2];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                $game1 = $this->getGlobalManager()->getGameEditorByName($game);
                if($game1->removeSlot($slot)) {
                    $sender->sendMessage(Msg::color("&aDeleted slot $slot for game $game."));
                }else{
                    $sender->sendMessage(Msg::color("&cSlot $slot not found for game $game."));
                }
            break;
            case "leave":
                $p = $sender;
                if($this->getStorage()->isPlayerSet($p)){
                    $game = $this->getStorage()->getPlayerGame($p);
                    if($game !== null) {
                        $this->getGlobalManager()->getGameManager($game)->removePlayer($p, true);
                        $p->sendMessage(Msg::color("&aExiting game..."));
                    }
                }
                elseif($this->getStorage()->isPlayerWaiting($p)){
                    $game = $this->getStorage()->getWaitingPlayerGame($p);
                    if($game !== null) {
                        $this->getGlobalManager()->getGameManager($game)->removeWaitingPlayer($p, true);
                        $p->sendMessage(Msg::color("&aExiting game..."));
                    }
                }else{
                    $p->sendMessage(Msg::color("&cYou are not playing on any game."));
                }
            break;
            case "lobby":
                if(!$sender->hasPermission("hg.command.lobby"))
					return false;
                if(empty($args[1])){
                    $sender->sendMessage(Msg::color("&a- /hg lobby <game>"));
					return false;
                }
                $game = $args[1];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                $this->getGlobalManager()->getGameEditorByName($game)->setLobbyPosition($sender);
                $sender->sendMessage(Msg::color("&aSuccessfully set lobby position where you are standing!"));
            break;
            case "dm":
                if(!$sender->hasPermission("hg.command.dm"))
					return false;
                if(empty($args[1])){
                    $sender->sendMessage(Msg::color("&a- /hg lobby <game>"));
					return false;
                }
                $game = $args[1];
                if(!$this->gameResourceExists($game) or !$this->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
					return false;
                }
                $this->getGlobalManager()->getGameEditorByName($game)->setDeathMatchPosition($sender);
                $sender->sendMessage(Msg::color("&aSuccessfully set death match position where you are standing!"));
            break;
        }
        return true;
    }

    /**
     * HungerGames base class
     *
     * @return Loader
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * HungerGames base folder
     *
     * @return string
     */
    public function dataPath(){
        return $this->getDataFolder();
    }

    /**
     * HungerGames arena folder
     *
     * @return string
     */
    public function getArenasDataPath(){
        return $this->dataPath()."arenas/";
    }

    /**
     * HungerGames resource data path
     *
     * @return string
     */
    public function getResourceDataPath(){
        return $this->dataPath()."resources/";
    }

    /**
     * Creates HungerGames resource name
     *
     * @param $resource
     * @param HungerGames $data
     */
    public function createGameResource($resource, HungerGames $data){
        file_put_contents($this->getResourceDataPath().$resource.".dat", serialize($data));
    }

    /**
     * Deletes HungerGames resource name
     *
     * @param $resource
     */
    public function deleteGameResource($resource){
        if($this->gameResourceExists($resource)) {
            unlink($this->getResourceDataPath() . $resource . ".dat");
        }
    }

    /**
     * Gets game resource by Id
     *
     * @param $resource
     * @return HungerGames|null
     */
    public function getGameResource($resource){
        if(file_exists($this->getResourceDataPath().$resource.".dat")){
            return unserialize(file_get_contents($this->getResourceDataPath() . $resource . ".dat"));
        }
        return null;
    }
    /**
     * Checks if game resource exists
     *
     * @param $resource
     * @return bool
     */
    public function gameResourceExists($resource){
        return file_exists($this->getResourceDataPath().$resource.".dat");
    }

    /**
     * Updates resource by recreating it
     *
     * @param $resource
     * @param HungerGames $data
     */
    public function updateResourceData($resource, HungerGames $data){
        $this->createGameResource($resource, $data);
    }

    /**
     * Gets all games
     *
     * @return HungerGames[]|null
     */
    public function getAllGameResources(){
        $data = [];
        $res = glob($this->getResourceDataPath()."*", GLOB_BRACE);
        foreach($res as $ret){
            if(substr($ret, strlen($ret)-4) === ".dat") {
                $data[] = unserialize(file_get_contents($ret));
            }
        }
        return $data === null ? null : $data;
    }

    /**
     * Delete HungerGames game arena
     *
     * @param HungerGames $game
     */
    public function deleteGameArena(HungerGames $game){
        unlink($this->getArenasDataPath().$game->getName().".yml");
    }

    /**
     * Gets all games
     *
     * @return Config[]
     */
    public function getAllGameArenas(){
        $data = [];
        $res = glob($this->getArenasDataPath()."*.yml", GLOB_BRACE);
        foreach ($res as $ret) {
            $data[] = new Config($ret, Config::YAML);
        }
        return $data === null ? null : $data;
    }

    /**
     * Checks if game arena exists
     *
     * @param $name
     * @return bool
     */
    public function gameArenaExists($name){
        return file_exists($this->getArenasDataPath().$name.".yml");
    }

    /**
     * Gets game arena by name
     *
     * @param $name
     * @return null|Config
     */
    public function getGameArenaByName($name){
        return file_exists($this->getArenasDataPath().$name.".yml") ? new Config($this->getArenasDataPath().$name.".yml", Config::YAML) : null;
    }

    /**
     * Game storage, game info is stored
     *
     * @return GameStorage
     */
    public function getStorage() : GameStorage{
        return $this->storage;
    }

    /**
     * Get global manager, manage all games
     *
     * @return GlobalManager
     */
    public function getGlobalManager() : GlobalManager{
        return $this->globalManager;
    }

    /**
     * Get script manager, manage all scripts
     *
     * @return HGAPIScriptManager
     */
    public function getScriptManager() : HGAPIScriptManager{
        return $this->scriptManager;
    }

    /**
     * Gets map backup to backup and reset maps
     *
     * @return MapBackup
     */
    public function getMapBackup() : MapBackup{
        return $this->mapBackup;
    }

    /**
     * Gets sign manager, to refresh all signs
     *
     * @return SignManager
     */
    public function getSignManager() : SignManager{
        return $this->signManager;
    }

    /**
     * Get all the messages
     *
     * @return string[]
     */
    public function getMessages(){
        return $this->messages->getAll();
    }

    /**
     * Gets message by index
     *
     * @param $message
     * @return string
     */
    public function getMessage($message){
        return $this->getMessages()[$message];
    }

    /**
     * Create HungerGames game arena
     *
     * @param HungerGames $game
     */
    public function createGameArena(HungerGames $game){
        $contents =
            [

                "sign_line_1" => "&6-=&e[&fS&cG&e]&6=-",
                "sign_line_2" => "&f{on}&f/&a{max}",
                "sign_line_3" => "&aGame: &f{game}",
                "sign_line_4" => "&eStatus: {status}",
                "is_sky_wars" => "no",
                "min_players" => (int)2,
                "max_players" => (int)8,
                "game_seconds" =>  (float)60*5,
                "waiting_seconds" => (float)60,
                "game_level" => "world",
                "refill_chest_after_seconds" => (float)60*2.5,
                "chest_items" =>
                    [
                        "272 0 1",
                        "298 0 1",
                        "299 0 1",
                        "300 0 1",
                        "301 0 1"
                    ],
                "lobby_pos" =>
                    [
                        "x" => (float)127,
                        "y" => (float)4,
                        "z" => (float)128,
                        "level" => "world"
                    ],
                "death_match_pos" =>
                    [
                        "x" => (float)140,
                        "y" => (float)4,
                        "z" => (float)150,
                        "level" => "world"
                    ],
                "slots" => [
                    "1" =>
                        [
                            "x" => (float)128,
                            "y" => (float)4,
                            "z" => (float)129,
                        ],
                    "2" =>
                        [
                            "x" => (float)138,
                            "y" => (float)4,
                            "z" => (float)139,
                        ],
                ],
                "sign_list" => []
            ];
        $c = new Config($this->getArenasDataPath().$game->getName().".yml", Config::YAML, $contents);
        $c->save();
    }
}
