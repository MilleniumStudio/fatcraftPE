<?php
namespace hungergames\command;
use hungergames\lib\utils\exc;
use hungergames\lib\utils\Info;
use hungergames\lib\utils\Msg;
use hungergames\Loader;
use hungergames\obj\HungerGames;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
class HGCommand extends Command implements PluginIdentifiableCommand{
    /** @var Loader */
    private $HGApi;
    public function __construct(Loader $main){
        parent::__construct("hg", "HungerGames ".Info::VERSION." command", exc::_("%%a/hg help"), ["sg", "sw"]);
        $this->HGApi = $main;
    }
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     */
    public function execute(CommandSender $sender, $commandLabel, array $args){
        if(!$sender instanceof Player){
            $sender->sendMessage(Msg::color("&aPlease run this command in-game."));
            return;
        }
        if(empty($args[0])){
            $sender->sendMessage(Msg::color("&a- /hg help"));
            return;
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
                if(!$sender->hasPermission("hg.command.add")) return;
                if(empty($args[1])){
                    $sender->sendMessage(Msg::color("&a- /hg add <game>"));
                    return;
                }
                $game = $args[1];
                if($this->HGApi->gameResourceExists($game) or $this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame already exists!"));
                    return;
                }
                $game1 = new HungerGames();
                $sender->sendMessage(Msg::color("&aCreating game $game... Please wait..."));
                $game1->loadGame($game1);
                $game1->create($game);
                $sender->sendMessage(Msg::color("&aSuccessfully created game $game!"));
            break;
            case "del":
                if(!$sender->hasPermission("hg.command.del")) return;
                if(empty($args[1])){
                    $sender->sendMessage(Msg::color("&a- /hg del <game>"));
                    return;
                }
                $game = $args[1];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                if(empty($args[2])){
                    $sender->sendMessage(Msg::color("&cAre you sure you want to delete $game? &4&lYOU CAN NOT GET IT BACK!!"));
                    $sender->sendMessage(Msg::color("&aIf you are sure please run: /hg del $game proceed"));
                    return;
                }
                if(strtolower($args[2]) !== "proceed"){
                    $sender->sendMessage(Msg::color("&aDid you mean \"/hg del $game\"?"));
                    return;
                }
                $game1 = $this->HGApi->getGameResource($game);
                $game1->delete(true);
                $sender->sendMessage(Msg::color("&cGame $game has been deleted! You can not get it back!"));
            break;
            case "min":
                if(!$sender->hasPermission("hg.command.min")) return;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg min <game> <number>"));
                    return;
                }
                $game = $args[1];
                $number = $args[2];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                if(!is_numeric($number)){
                    $sender->sendMessage(Msg::color("&cInvalid int/number value."));
                    return;
                }
                $game1 = $this->HGApi->getGlobalManager()->getGameEditorByName($game);
                $game1->setMinimumPlayers($number);
                $sender->sendMessage(Msg::color("&cMinimum players of game $game have been set to $number."));
            break;
            case "max":
                if(!$sender->hasPermission("hg.command.max")) return;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg max <game> <number>"));
                    return;
                }
                $game = $args[1];
                $number = $args[2];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                if(!is_numeric($number)){
                    $sender->sendMessage(Msg::color("&cInvalid int/number value."));
                    return;
                }
                $game1 = $this->HGApi->getGlobalManager()->getGameEditorByName($game);
                $game1->setMaximumPlayers($number);
                $sender->sendMessage(Msg::color("&aMaximum players of game $game have been set to $number."));
            break;
            case "level":
                if(!$sender->hasPermission("hg.command.level")) return;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg level <game> <level name>"));
                    return;
                }
                $game = $args[1];
                $level = $args[2];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                $loaded = $this->HGApi->getServer()->isLevelLoaded($this->HGApi->getServer()->getLevelByName($level));
                $check = $this->HGApi->getServer()->loadLevel($level);
                if(!$loaded){
                    if($check){
                        $game1 = $this->HGApi->getGlobalManager()->getGameEditorByName($game);
                        $game1->setGameLevel($level);
                        $sender->sendMessage(Msg::color("&aSet game level of $game to $level."));
                        return;
                    }else{
                        $sender->sendMessage(Msg::color("&cCould not find any level with name $level."));
                        return;
                    }
                }
            break;
            case "ws":
                if(!$sender->hasPermission("hg.command.ws")) return;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg ws <game> <seconds>"));
                    return;
                }
                $game = $args[1];
                $seconds = $args[2];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                if(!is_numeric($seconds)){
                    $sender->sendMessage(Msg::color("&cInvalid int/number value."));
                    return;
                }
                $game1 = $this->HGApi->getGlobalManager()->getGameEditorByName($game);
                $game1->setWaitingSeconds($seconds);
                $sender->sendMessage(Msg::color("&aSet waiting seconds of game $game to $seconds."));
            break;
            case "gs":
                if(!$sender->hasPermission("hg.command.ws")) return;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg gs <game> <seconds>"));
                    return;
                }
                $game = $args[1];
                $seconds = $args[2];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                if(!is_numeric($seconds)){
                    $sender->sendMessage(Msg::color("&cInvalid int/number value."));
                    return;
                }
                $game1 = $this->HGApi->getGlobalManager()->getGameEditorByName($game);
                $game1->setWaitingSeconds($seconds);
                $sender->sendMessage(Msg::color("&aSet game seconds of game $game to $seconds."));
            break;
            case "addslot":
                if(!$sender->hasPermission("hg.command.slot.add")) return;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg addslot <game> <name>"));
                    return;
                }
                $game = $args[1];
                $slot = $args[2];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                $game1 = $this->HGApi->getGlobalManager()->getGameEditorByName($game);
                $game1->addSlot($sender, $slot);
                $sender->sendMessage(Msg::color("&aAdded slot $slot for game $game."));
            break;
            case "delslot":
                if(!$sender->hasPermission("hg.command.slot.del")) return;
                if(empty($args[1]) or empty($args[2])){
                    $sender->sendMessage(Msg::color("&a- /hg delslot <game> <name>"));
                    return;
                }
                $game = $args[1];
                $slot = $args[2];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                $game1 = $this->HGApi->getGlobalManager()->getGameEditorByName($game);
                if($game1->removeSlot($slot)) {
                    $sender->sendMessage(Msg::color("&aDeleted slot $slot for game $game."));
                }else{
                    $sender->sendMessage(Msg::color("&cSlot $slot not found for game $game."));
                }
            break;
            case "leave":
                $p = $sender;
                if($this->HGApi->getStorage()->isPlayerSet($p)){
                    $game = $this->HGApi->getStorage()->getPlayerGame($p);
                    if($game !== null) {
                        $this->HGApi->getGlobalManager()->getGameManager($game)->removePlayer($p, true);
                        $p->sendMessage(Msg::color("&aExiting game..."));
                    }
                }
                elseif($this->HGApi->getStorage()->isPlayerWaiting($p)){
                    $game = $this->HGApi->getStorage()->getWaitingPlayerGame($p);
                    if($game !== null) {
                        $this->HGApi->getGlobalManager()->getGameManager($game)->removeWaitingPlayer($p, true);
                        $p->sendMessage(Msg::color("&aExiting game..."));
                    }
                }else{
                    $p->sendMessage(Msg::color("&cYou are not playing on any game."));
                }
            break;
            case "lobby":
                if(!$sender->hasPermission("hg.command.lobby")) return;
                if(empty($args[1])){
                    $sender->sendMessage(Msg::color("&a- /hg lobby <game>"));
                    return;
                }
                $game = $args[1];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                $this->HGApi->getGlobalManager()->getGameEditorByName($game)->setLobbyPosition($sender);
                $sender->sendMessage(Msg::color("&aSuccessfully set lobby position where you are standing!"));
            break;
            case "dm":
                if(!$sender->hasPermission("hg.command.dm")) return;
                if(empty($args[1])){
                    $sender->sendMessage(Msg::color("&a- /hg lobby <game>"));
                    return;
                }
                $game = $args[1];
                if(!$this->HGApi->gameResourceExists($game) or !$this->HGApi->gameArenaExists($game)){
                    $sender->sendMessage(Msg::color("&cGame does not exist!"));
                    return;
                }
                $this->HGApi->getGlobalManager()->getGameEditorByName($game)->setDeathMatchPosition($sender);
                $sender->sendMessage(Msg::color("&aSuccessfully set death match position where you are standing!"));
            break;
        }
    }
    /**
     * @return Loader
     */
    public function getPlugin(){
        return $this->HGApi;
    }
}
