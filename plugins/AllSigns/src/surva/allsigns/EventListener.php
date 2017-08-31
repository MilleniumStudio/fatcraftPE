<?php
/**
 * Created by PhpStorm.
 * User: surva
 * Date: 14.05.16
 * Time: 12:01
 */

namespace surva\allsigns;

use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\command\ConsoleCommandSender;

class EventListener implements Listener {
    /* @var AllSigns */
    private $allSigns;

    public function __construct(AllSigns $allSigns) {
        $this->allSigns = $allSigns;
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if($block->getId() == Block::SIGN_POST OR $block->getId() == Block::WALL_SIGN) {
            $tile = $block->getLevel()->getTile($block);

            if($tile instanceof Sign) {
                $text = $tile->getText();

				$configFile = $this->getAllSigns()->getConfig();

				switch($text[0]) {

					case $configFile->get("world"):
                        $level = $this->getAllSigns()->getServer()->getLevelByName($text[1]);

                        if($level instanceof Level) {
                            $tile->setText($configFile->get("worldtext"), $text[1], $text[2], count($level->getPlayers()) . " " . $this->getAllSigns()->getConfig()->get("players"));
                        } else {
                            $block->getLevel()->setBlock($block, Block::get(Block::AIR));

                            $player->sendMessage($configFile->get("noworld"));
                        }
                        break;

					case $configFile->get("command"):
						$tile->setText($configFile->get("commandtext"), $text[1], $text[2], $text[3]);
						break;

					case $configFile->get("tpNextLevel"):
						$tile->setText($configFile->get("tpNextLevelMessage1"), $configFile->get("tpNextLevelMessage2"),$configFile->get("Obf")."tp ". $text[1], "");
						break;

					case $configFile->get("addCheckpoint"):
						$tile->setText($configFile->get("Obf")."checkpoint", $configFile->get("checkpoint"),"", "");
						break;

					case $configFile->get("goToGame"):
						$tile->setText($configFile->get("Obf").$text[1], $configFile->get("goToGameMessage1"), $configFile->get("goToGameMessage2"), $configFile->get("Obf").$text[2]);
						break;

					case $configFile->get("goToLobby"):
						$tile->setText($configFile->get("Obf").$text[1], $configFile->get("goBackLobbyMessage1"), $configFile->get("goBackLobbyMessage2"), $configFile->get("Obf").$text[2]);
						break;

                    case $configFile->get("worldtext"):
                        $level = $this->getAllSigns()->getServer()->getLevelByName($text[1]);

                        if($level instanceof Level) {
                            $player->teleport($level->getSafeSpawn());
                        } else {
                            $player->sendMessage($configFile->get("noworld"));
                        }
                        break;

					case $configFile->get("commandtext"):
						$this->getAllSigns()->getServer()->dispatchCommand($player, $text[2] . $text[3]);
						break;

					case $configFile->get("tpNextLevelMessage1"):
						$command = 'tp ' . $player->getName() . ' ' . substr($text[2], 6);
						var_dump($command);
						$this->getAllSigns()->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
						break;

					default:
						var_dump($text[1]);
						if ($text[1] == $configFile->get("checkpoint")){
							$command = "spawnpoint " . $player->getName() . " " . $player->getPosition()->getX() . " " . $player->getPosition()->getY() . " " . $player->getPosition()->getZ();
							$this->getAllSigns()->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
						}

						if ($text[1] == $configFile->get("goToGameMessage1") || $text[1] == $configFile->get("goBackLobbyMessage1")){
							$command = substr($text[0], 3) . " " . $player->getName() . " " . substr($text[3], 3);
							var_dump($command);
							$this->getAllSigns()->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
						}
				}
            }
        }
    }

    /**
     * @return AllSigns
     */
    public function getAllSigns(): AllSigns {
        return $this->allSigns;
    }
}
