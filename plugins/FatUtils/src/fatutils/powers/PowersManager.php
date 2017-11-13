<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 06/11/2017
 * Time: 18:01
 */

namespace fatutils\powers;

use fatutils\FatUtils;
use fatutils\pets\PetsManager;
use fatutils\tools\particles\ParticleBuilder;
use fatutils\tools\volume\CuboidVolume;
use fatutils\tools\WorldUtils;
use pocketmine\block\BlockIds;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Location;
use pocketmine\level\particle\Particle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\utils\Config;

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

        // load config
        $config = FatUtils::getInstance()->getTemplateConfig();

        foreach ($config->getNested("powerPlaces") as $item) {
            $loc = WorldUtils::stringToLocation($item);
            $loc = Location::fromObject($loc->add(0.5, 0.5, 0.5), $loc->level);
            $this->placePower($loc);
        }

        //todo for debug
        $this->availablePowers[] = "Boost";
        $this->availablePowers[] = "Shot";
        $this->availablePowers[] = "Mine";
        $this->availablePowers[] = "Blindness";
    }

    //=======================================================================
    //=======================================================================
    const SLOT = 4;
    const PLACES_SIZE = 2;

    private $powersPlaces = [];
    private $availablePowers = [];
    private $activePowers = [];

    public function placePower(Location $location, int $size = self::PLACES_SIZE, bool $stayForever = false)
    {
        $this->powersPlaces[] = [new CuboidVolume(
            new Location($location->getX() - $size / 2, $location->getY() - $size / 2, $location->getZ() - $size / 2, 0, 0, $location->level),
            new Location($location->getX() + $size / 2, $location->getY() + $size / 2, $location->getZ() + $size / 2, 0, 0, $location->level)
        ), $stayForever, $location];
    }

    public function equipPlayer(Player $player)
    {
        if ($player->getInventory()->getHotbarSlotItem(self::SLOT)->getId() == BlockIds::AIR) {
            $className = "fatutils\\powers\\effects\\" . $this->availablePowers[array_rand($this->availablePowers)];
            /** @var APower $power */
            $power = new $className($player);
            $this->activePowers[$power->getUniqueId()] = $power;
        }
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
                if (array_key_exists($item->getNamedTag()->powerKey . "", $this->activePowers)) {
                    /** @var APower $power */
                    $power = $this->activePowers[$item->getNamedTag()->powerKey . ""];
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
        if (!($sender instanceof Player)) {
            echo "need to be a player to execute this command\n";
            return true;
        }
        switch ($args[0]) {
            case "add": {
                $this->equipPlayer($sender);
            }
                break;
            case "target": {
                PetsManager::getInstance()->spawnPet($sender, "Zombie", false)->equip();
            }
                break;
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
            $base = new Vector3(0, 0, 1);
//            $particle = ParticleBuilder::fromParticleId(Particle::TYPE_REDSTONE);
            $particle = ParticleBuilder::fromParticleId(Particle::TYPE_CRITICAL);
            for ($i = 0; $i < 15; $i++) {
                $yaw = rand(0, 360);
                $pitch = rand(0, 360);

                $v1 = $base->asVector3();
                $v1->x = 0;
                $v1->y = -sin(deg2rad($pitch));
                $v1->z = cos(deg2rad($pitch));
                $v2 = $v1->asVector3();
                $v2->x = -$v1->z * sin(deg2rad($yaw));
                $v2->z = $v1->z * cos(deg2rad($yaw));

                $v2 = $v2->multiply(0.7);

                $particle->play(Position::fromObject($place[2]->add($v2), $place[2]->level));
            }
        }
    }
}