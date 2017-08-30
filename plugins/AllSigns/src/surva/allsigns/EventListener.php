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
				var_dump($text[2]);

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
						var_dump($configFile->get("Obf")."checkpoint");

						$tile->setText($configFile->get("Obf")."checkpoint", $configFile->get("checkpoint"),"", "");
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
						$test = substr($text[2], 3);
						$this->getAllSigns()->getServer()->dispatchCommand($player, $test);
						var_dump($test);
						break;

					default:
						if ($text[1] == $configFile->get("checkpoint"))
							$this->getAllSigns()->getServer()->dispatchCommand($player, "spawnpoint");
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
