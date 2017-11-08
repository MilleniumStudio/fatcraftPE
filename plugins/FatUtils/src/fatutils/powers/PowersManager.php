<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 06/11/2017
 * Time: 18:01
 */

namespace fatutils\powers;

use fatutils\FatUtils;
use fatutils\tools\particles\ParticleBuilder;
use fatutils\tools\volume\CuboidVolume;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class PowersManager implements Listener, CommandExecutor
{

    private static $m_Instance;

    public static function getInstance(): PowersManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new PowersManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new PowerChecker(FatUtils::getInstance()), 2);

        //todo for debug
        $this->availablePowers[] = "Boost";
    }

    //=======================================================================

    private $powersPlaces = [];
    private $availablePowers = [];
    private $activePowers = [];

    public function placePower(Location $location, int $size = 2, bool $stayForever = false)
    {
        $this->powersPlaces[] = [new CuboidVolume(
            new Location($location->getX() - $size / 2, $location->getY() - $size / 2, $location->getZ() - $size / 2, 0, 0, $location->level),
            new Location($location->getX() + $size / 2, $location->getY() + $size / 2, $location->getZ() + $size / 2, 0, 0, $location->level)
        ), $stayForever];
    }

    public function equipPlayer(Player $player)
    {
        $className = "fatutils\\powers\\effects\\" . $this->availablePowers[array_rand($this->availablePowers)];
        /** @var APower $power */
        $power = new $className($player);
        $this->activePowers[$power->getUniqueId()] = $power;
    }

    public function getPowersPlaces()
    {
        return $this->powersPlaces;
    }

    //==================================================================
    // Events
    //==================================================================
    public function onUse(PlayerInteractEvent $event)
    {
        $item = $event->getItem();
        if ($item != null) {
            if (isset($item->getNamedTag()->powerKey)) {
                if (array_key_exists($item->getNamedTag()->powerKey."", $this->activePowers)) {
                    /** @var APower $power */
                    $power = $this->activePowers[$item->getNamedTag()->powerKey.""];
                    if ($power != null) {
                        $event->setCancelled(true);
                        $power->action();
                    }
                }
            }
        }
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param string[] $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($args[0]) {
            case "add": {
                if ($sender instanceof Player)
                    $this->equipPlayer($sender);
                else
                    echo "need to be a player to execute this command\n";
            }
        }
        return true;
    }

}

class PowerChecker extends PluginTask
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
        $places = PowersManager::getInstance()->getPowersPlaces();
        foreach ($places as $place) {
            /** @var CuboidVolume $volume */
            $volume = $place[0];
            // check players/powers positions
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($volume->isIn($player)) {
                    PowersManager::getInstance()->equipPlayer($player);
                }
            }
            // refresh display
            $volume->display();
        }
    }
}