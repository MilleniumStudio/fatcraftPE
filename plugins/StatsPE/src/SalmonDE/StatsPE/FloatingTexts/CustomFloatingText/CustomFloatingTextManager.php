<?php
namespace SalmonDE\StatsPE\FloatingTexts\CustomFloatingText;

use Exception;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use SalmonDE\StatsPE\Base;

/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 13/09/2017
 * Time: 11:58
 */
class CustomFloatingTextManager
{
    private static $instance;
    public $plugin;
    public $task;
    public $customFloatingTexts;

    public static function add(CustomFloatingText $customFloatingText)
    {
        if(CustomFloatingTextManager::$instance == null)
            new CustomFloatingTextManager();
        CustomFloatingTextManager::$instance->customFloatingTexts[] = $customFloatingText;
    }

    public static function getInstance() : CustomFloatingTextManager
    {
        if(CustomFloatingTextManager::$instance == null)
            new CustomFloatingTextManager();
        return CustomFloatingTextManager::$instance;
    }

    private function __construct()
    {
        CustomFloatingTextManager::$instance = $this;
        $this->plugin = Base::getInstance();
        $this->loadConfigs();
        $this->task = new CustomFloatingTextTask($this->plugin);

        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($this->task, 1);
    }

    public function loadConfigs()
    {
        // load config for FloatingTops
        $config = $this->plugin->getConfig()->get("FloatingTops");
        if ($config == null)
            return;
        foreach ($config as $key => $value) {
            $statName = isset($value['statName'])?$value['statName']:null;
            if($statName==null)
                throw new Exception("StatsPE Error: you need to specify a statName to use a FloatingTop");
            $location = isset($value['location'])?$value['location']:null;
            if($location == null)
                throw new Exception("StatsPE Error: you need to specify a location to use a FloatingTop");
            $customName = isset($value['name'])?$value['name']:null;
            $nbLines = (int)isset($value['lines'])?$value['lines']:5;
            $location = explode("/", $location);
            if (Server::getInstance()->getLevelByName($location[0]) == null)
                throw new Exception("StatsPE Error: world not found");
            new FloatingTop($statName, $location[1], $location[2], $location[3], Server::getInstance()->getLevelByName($location[0]), $nbLines, $customName);
        }
    }
}

class CustomFloatingTextTask extends PluginTask{
    public function __construct(Plugin $owner)
    {
        parent::__construct($owner);
    }
    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        /** @var CustomFloatingText $floatingText */
        $manager = CustomFloatingTextManager::getInstance();
        if($manager->customFloatingTexts!= null) {
            foreach ($manager->customFloatingTexts as $floatingText) {
                if ($floatingText->needUpdate($currentTick)) {
                    $floatingText->update();
                }
            }
        }
    }
}