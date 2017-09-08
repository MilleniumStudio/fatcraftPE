# HungerGames
=============

BIG NOTE
---------
COMPILING IT WITH pmt.mcpe.me WILL MAKE THE PLUGIN CORRUPTED. DOWNLOAD FROM _RELEASES_ [HERE](https://github.com/InfinityGamers/HungerGames-UPDATED/releases/latest)

A HungerGames plugin for PocketMine-MP developed by xBeastMode
--------------------------------------------------------------

#New Features? 
Yes, of course. You can choose between SkyWars or HungerGames!
How? You can make a game with the following command: /hg add <game name>
Then, run the command "/reload", and check the config inside the folder "arenas", inside the plugin folder. Configure it to your needs, there you will find the "is_sky_wars" option, set it to true to make it skywars ;)

Watch my YouTube video showing bow to setup a game arena:
[![MCPE - HG plugin tutorial updated](http://img.youtube.com/vi/0Jtt696xak4/0.jpg)](http://www.youtube.com/watch?v=0Jtt696xak4)
<h3> 
When you join a game the plugin will automatically backup the map.
</h3>

- Other features: Scripts have been added. I did not make any scripts for it yet, maybe in the future.

#How to setup a join sign?

on the first line set the line to "hg" and on the second line set the line to the name of your game.
Next, reload your server with the "/reload" command.
The sign should now refresh automatically and you'll be able to join.

#Future updates? 
I am planning to adding many more features to this plugin, if you wish me to add one, please say it in issues, thank you.

- For Devs:

This plugin comes with a script loader api. You can use this to access game functions, like when player joins, quits, wins, etc. You do not need to enable it, as it loads itself.

<h3><div style="font-family: verdana, sans-serif;">If you wish to create one here's an example code:</div><h3>
<details>
<summary>Click here to view example:</summary>

```PHP
//Example script:


<?php
class ExampleScript extends \hungergames\api\scripts\HGAPIScript{
    public function __construct(){
        parent::__construct("Script names here", "Versions here 1.0", "Authors here xBeastMode");
    }
    public function onLoad(){
        $this->sendConsoleMessage("Test script loaded!");
    }
}


//All function from this script api are:

/**
     * Creates script config
     *
     * @param $name
     * @param array $values
     * @return Config
     */
    public void function createConfig($name, array $values)
    /**
     * Gets script config
     *
     * @return Config
     */
    public Config function getConfig()
    /**
     * Gets the name of the script
     *
     * @return string
     */
    public string function getName()
    /**
     * Gets the name of the script
     *
     * @return string
     */
    public string function getVersion()
    /**
     * Gets the author of the script
     *
     * @return string
     */
    public string function getAuthor()
    /**
     * disables script
     */
    public void function setDisabled()
    /**
     * enables script
     */
    public void function setEnabled();
    /**
     * returns whether script is enabled or not
     *
     * @return bool
     */
    public bool function isEnabled()
    /**
     * Sends console message
     *
     * @param $message
     */
    public void function sendConsoleMessage($message)
    /**
     * Called when script is loaded
     */
    public function onLoad(){
    //your code here
    }
    /**
     * called when player joins game
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function onPlayerJoinGame(Player $p, HungerGames $game){
    //your code here
    }
    /**
     * called when player quits game
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function onPlayerQuitGame(Player $p, HungerGames $game){
    //your code here
    }
    /**
     * Called when player fails to join full game
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function gameIsFull(Player $p, HungerGames $game){
    //your code here
    }

    /**
     * Called when player is waiting for players
     *
     * @param array $players
     * @param HungerGames $game
     */
    public function whileWaitingForPlayers(array $players, HungerGames $game){
    //your code here
    }
    /**
     * Called when player is waiting for players
     *
     * @param array $players
     * @param HungerGames $game
     */
    public function whileWaitingToStart(array $players, HungerGames $game){
    //your code here
    }
    /**
     * Called when game starts
     *
     * @param array $players
     * @param HungerGames $game
     */
    public function onGameStart(array $players, HungerGames $game){
    //your code here
    }
    /**
     * Called when death match starts
     *
     * @param array $players
     * @param HungerGames $game
     */
    public function onDeathMatchStart(array $players, HungerGames $game){
    //your code here
    }
    /**
     * Called when players wins a game
     *
     * @param Player $p
     * @param HungerGames $game
     */
    public function onPlayerWinGame(Player $p, HungerGames $game){
    //your code here
    }information
```
</details>

===
BIG NOTE: after every change you will have to run the command /reload
===

Commands:

* /hg add <game> : adds a new game
  * OP perm: hg.command.add
    
* /hg del <game> : deletes a game
  * OP perm: hg.command.del
  
* /hg min <game> <number> : changes the number of minimum players required to start a game
  * OP perm: hg.command.min
  
  
* /hg max <game> <number> : changes number of maximum players that can enter a game
  * OP perm: hg.command.max

* /hg level <game> <level name> : changes level of game where players are gonna go
  * OP perm: hg.command.level

* /hg ws <game> <number> : sets amount of seconds to wait before game starts  
  * OP perm: hg.command.ws

* /hg gs <game> <number> : sets amount of second to pass before death match starts
  * OP perm: hg.command.gs

* /hg addslot <game> <name> : adds new slot to game (positions sets where you are standing)
  * OP perm: hg.command.slot.add

* /hg delslot <game> <name> : deletes slot from game by name
  * OP perm: hg.command.slot.del

* /hg leave : leaves game that you are playing
  * OP perm: none
