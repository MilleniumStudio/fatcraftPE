<?php

namespace fatutils\pets;

use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use fatutils\shop\ShopItem;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 17/10/2017
 * Time: 13:56
 */
class PetsManager implements Listener, CommandExecutor
{
    private static $m_Instance = null;

    public static function getInstance(): PetsManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new PetsManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new OnTick(FatUtils::getInstance()), 5);
    }

    public function updatePets(){
        /** @var FatPlayer $player */
        foreach (PlayersManager::getInstance()->getFatPlayers() as $player) {
            $pet = $player->getSlot(ShopItem::SLOT_PET);
            if($pet != null && $pet instanceof Pet){
                $pet->updatePosition();
            }
        }
    }

    public function spawnPet(Player $player, $petTypes): ShopItem
    {
        $fatPlayer = PlayersManager::getInstance()->getFatPlayer($player);
        $pet = new Pet($fatPlayer, $petTypes);
        $fatPlayer->setSlot(ShopItem::SLOT_PET, $pet);
        return $pet;
    }

    /**
     * @param \pocketmine\command\CommandSender $sender
     * @param \pocketmine\command\Command $command
     * @param string $label
     * @param string[] $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof \pocketmine\Player) {
            switch ($args[0]) {
                case "spawn": {
                    $this->spawnPet($sender, PetTypes::VILLAGER)->equip();
                    echo "pet spawn\n";
                }
                    break;
                case "kill": {
                    PlayersManager::getInstance()->getFatPlayer($sender)->getSlot(ShopItem::SLOT_PET)->unequip();
                    echo "pet killed\n";
                }
                    break;
            }
        } else {
            echo "Commands only available as a player\n";
        }
        return true;
    }
}

//===============================================
class OnTick extends PluginTask
{
    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        PetsManager::getInstance()->updatePets();
    }
}