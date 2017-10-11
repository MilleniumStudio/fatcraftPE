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
    private $m_activePerms = [];

    public static function getInstance(): PermissionManager
    {
        if (is_null(self::$m_Instance))
            self::$m_Instance = new PermissionManager();
        return self::$m_Instance;
    }

    private function __construct()
    {
        FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents($this, FatUtils::getInstance());
        FatUtils::getInstance()->getCommand("perms")->setExecutor(new PermCommands());
        $this->loadFromConfig();
    }

    public function loadFromConfig()
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
        if ($p_player instanceof FatPlayer) {
            $p = $p_player->getPlayer();
            $groupName = $p_player->getPermissionGroup();
            if (array_key_exists($groupName, $this->m_processedPerms)) {
                $attachement = null;
                if (!array_key_exists($p->getUniqueId()->toString(), $this->m_activePerms)) {
                    $attachement = $p->addAttachment(FatUtils::getInstance());
                    $this->m_activePerms[$p->getUniqueId()->toString()] = $attachement;
                }
                $attachement = $this->m_activePerms[$p->getUniqueId()->toString()];
                $attachement->clearPermissions();
                $attachement->setPermissions($this->m_processedPerms[$groupName]);
            } else {
                echo "Unknown permissionGroup " . $groupName . "\n";
            }
            // add prefix to display name
            if (array_key_exists($groupName, $this->m_prefix))
                $p->setDisplayName("[" . $this->m_prefix[$groupName] . "]" . $p->getName());
        }
    }

    public function listPerms(string $groupName): string
    {
        if (array_key_exists($groupName, $this->m_processedPerms)) {
            return print_r($this->m_processedPerms[$groupName], true);
        } else {
            return "This group doesn't exists";
        }
    }

    public function clearPerms(FatPlayer $fatPlayer){
        if (!array_key_exists($fatPlayer->getUniqueId()->toString(), $this->m_activePerms)) {
            array_splice($this->m_activePerms, $fatPlayer->getUniqueId()->toString(), 1);
        }
    }
}
