<?php

namespace fatutils\permission;

use fatutils\EventListener;
use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\utils\Config;

class PermissionManager extends EventListener
{
    private static $m_Instance = null;
    private $m_permissions = [];
    private $m_processedPerms = [];
    private $m_prefix = [];

    public static function getInstance(): PermissionManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new PermissionManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
        $this->loadFromConfig();
    }

    private function loadFromConfig()
    {
        $config = new Config(FatUtils::getInstance()->getDataFolder() . "permissions.yml", Config::YAML);
        $this->m_permissions = $config->getAll();
        foreach ($this->m_permissions as $key => $value) {
            $this->m_processedPerms[$key] = [];
            foreach ($value as $k => $v) {
                if ($k == "prefix") {
                    $this->m_prefix[$key] = $v;
                } else if ($k == "extends") {
                    $this->m_processedPerms[$key] = $this->m_processedPerms[$v];
                } else if ($k == "allow") {
                    foreach ($v as $item) {
                        $this->m_processedPerms[$key][$item] = true;
                    }
                } else if ($k == "deny") {
                    foreach ($v as $item) {
                        $this->m_processedPerms[$key][$item] = false;
                    }
                }
            }
        }
        echo "================================================\n";
        print_r($this->m_processedPerms);
        echo "================================================\n";
    }

    public function updatePermissions(FatPlayer $p_player)
    {
        $p = $p_player->getPlayer();
        $groupName = $p_player->getPermissionGroup();
        if (array_key_exists($groupName, $this->m_processedPerms)) {
            $attachement = $p->addAttachment(FatUtils::getInstance());
            $attachement->clearPermissions();
            foreach ($this->m_processedPerms[$groupName] as $permName => $value) {
                $attachement->setPermission($permName, $value);
            }
        } else {
            echo "Unknown permissionGroup " . $groupName . "\n";
        }

        // affichage prefix head
        // and in chat
    }
}
