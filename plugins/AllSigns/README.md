# AllSigns
Turn signs into world teleport and command signs

![](https://poggit.pmmp.io/ci.badge/survanetwork/AllSigns/AllSigns)

[Get the latest AllSigns artifacts (PHAR file) here](https://poggit.pmmp.io/ci/survanetwork/AllSigns/AllSigns)

## Creating signs
You can create world signs to teleport the player to a world or command signs which are running a specific command when the player is touching it.

### World
To create a world sign which is teleporting the player to a specific world when he is touching it and is showing the players count in the world, just create a sign like this and touch it.

![](http://i.imgur.com/UbEQBJE.png)

So write the sign like that:  
1. world  
2. the name of the world  
3. anything like a description of the world  
4. nothing  

### Command
To create a command sign which is executing a specific command when he is touching it, just create a sign like this and touch it.

![](http://i.imgur.com/1EqidAN.png)

Write the sign like that:  
1. command  
2. anything like a description of the command  
3. the first part of the command  
4. the second part of the command  

So when you are writing your sign like this, it'll execute the command "help".  
3. help  
4. nothing  

And when you're writing your sign like that, it'll also execute the command "help".  
3. he  
4. lp  

## Config

```yaml
# Sign commands and text
world: "world" # When you create a world teleport sign, you need to write that in the first line
worldtext: "§9World" # This will be written in the first line when you created the sign
players: "players" # This is showing the players count of the world, like 7 players

command: "command" # When you create a command sign, you need to write that in the first line
commandtext: "§aCommand" # This will be written in the first line when you created the sign

# Messages
noworld: "§cWorld does not exist" # Message which is sent to the player when a world does not exist
error: "§cError" # Text which is shown on the sign at the players count when the world does not exist
```

## License & Credits
[![Creative Commons License](https://i.creativecommons.org/l/by-sa/4.0/88x31.png)](http://creativecommons.org/licenses/by-sa/4.0/)

You are free to copy, redistribute, change or expand our work, but you must give credits share it under the same license.
[AllSigns](https://github.com/survanetwork/AllSigns) by [surva network](https://github.com/survanetwork) is licensed under a [Creative Commons Attribution-ShareAlike 4.0 International License](http://creativecommons.org/licenses/by-sa/4.0/).
