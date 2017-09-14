<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 14/09/2017
 * Time: 13:51
 */

namespace fatcraft\lobby;

use fatutils\FatUtils;
use fatutils\tools\Timer;
use fatutils\tools\WorldUtils;
use fatutils\tools\DelayedExec;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Lobby extends PluginBase implements Listener
{
    const CONFIG_KEY_WELCOME_TITLE = "welcomeTitle";
    const CONFIG_KEY_WELCOME_SUBTITLE = "welcomeSubtitle";

    private static $m_Instance;

    public static function getInstance(): Lobby
    {
        return self::$m_Instance;
    }

    public function onLoad()
    {
        self::$m_Instance = $this;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        FatUtils::getInstance()->setTemplateConfig($this->getConfig());
        WorldUtils::stopWorldsTime();
    }

    public function onPlayerJoin(PlayerJoinEvent $e)
    {
        new DelayedExec(5, function () use ($e) {
            $e->getPlayer()->addTitle($this->getConfig()->get(Lobby::CONFIG_KEY_WELCOME_TITLE, ""));
            $e->getPlayer()->addSubTitle($this->getConfig()->get(Lobby::CONFIG_KEY_WELCOME_SUBTITLE, ""));
        });
    }

    public function onPlayerDamage(EntityDamageEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player)
            $e->setCancelled(true);
    }
}