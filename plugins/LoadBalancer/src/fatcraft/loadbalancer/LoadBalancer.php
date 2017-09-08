<?php

namespace fatcraft\loadbalancer;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use \pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
//use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\PluginTask;
use libasynql\ClearMysqlTask;
use libasynql\result\MysqlResult;
use libasynql\result\MysqlSelectResult;
use libasynql\DirectQueryMysqlTask;
use libasynql\MysqlCredentials;

class LoadBalancer extends PluginBase implements Listener
{

    private static $m_Instance;
    private $m_Langs;
    private $m_ServerUUID;
    private $m_ServerType;
    private $m_Serverid;
    private $m_ServerState = "closed";

    /** @var \mysqli */
    private $m_Mysql;

    /** @var MysqlCredentials */
    private $m_Credentials;
    private $m_Servers = array();
    private $m_TotalPlayers = 0;
    private $m_MaxPlayers = 0;

    public function onLoad()
    {
        // registering instance
        LoadBalancer::$m_Instance = $this;

        // Config section
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        // Language section
        $this->saveResource("language.properties");
        $this->lang = new Config($this->getDataFolder() . "language.properties", Config::PROPERTIES);
    }

    public function onEnable()
    {
        // register events listener
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // init mysql
        $this->m_Credentials = $cred = MysqlCredentials::fromArray($this->getConfig()->get("mysql"));
        $this->m_Mysql = $cred->newMysqli();

        $this->m_ServerUUID = $this->m_Mysql->escape_string($this->getServer()->getServerUniqueId());
        $this->m_ServerType = $this->config->getNested("node.type");
        $this->m_ServerId = $this->config->getNested("node.id");

        $this->getLogger()->info("Config : node -> " . $this->m_ServerType . "-" . $this->m_ServerId);
        $this->getLogger()->info("Server uinique ID : " . $this->m_ServerUUID);

        //init database
        $this->initDatabase();

        // update my status every second
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new class($this) extends PluginTask
        {

            public function onRun(int $currentTick)
            {
                LoadBalancer::getInstance()->updateMe();
            }
        }, 0, 20);

        //update other server status every seconds too
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new class($this) extends PluginTask
        {

            public function onRun(int $currentTick)
            {
//                LoadBalancer::getInstance()->cleanOrphaned();
                LoadBalancer::getInstance()->getOthers();
            }
        }, 20, 20);
        $this->getLogger()->info("Enabled");
    }

    public function onDisable()
    {
        if (isset($this->m_Credentials))
        {
            $this->deleteMe();
            ClearMysqlTask::closeAll($this, $this->m_Credentials);
        }
    }

    public static function getInstance(): LoadBalancer
    {
        if (LoadBalancer::$m_Instance === null)
        {
            LoadBalancer::$m_Instance = $this;
        }
        return LoadBalancer::$m_Instance;
    }

    public function connectMainThreadMysql(): \mysqli
    {
        return $this->m_Mysql;
    }

    public function getCredentials(): MysqlCredentials
    {
        return $this->m_Credentials;
    }

    private function initDatabase()
    {
        $this->m_Mysql->query("CREATE TABLE IF NOT EXISTS servers (
            sid CHAR(36) PRIMARY KEY,
            type VARCHAR(20),
            id INT(11),
            ip VARCHAR(15),
            port SMALLINT,
            status VARCHAR(63),
            online SMALLINT,
            max SMALLINT,
            laston TIMESTAMP
        )");

//        $this->m_Mysql->query("INSERT INTO servers (sid, type, id, ip, port, online, max, laston) VALUES ('{$this->m_Mysql->escape_string($this->getServer()->getServerUniqueId())}', '{$this->config->get("node.type")}', '{$this->config->get("node.id")}', '{$this->m_Mysql->escape_string($this->config->get("external_ip"))}', {$this->getServer()->getPort()}, 0, {$this->getServer()->getMaxPlayers()}, unix_timestamp())");
//        $this->m_Mysql->query("CREATE TABLE IF NOT EXISTS transferts (
//            id INT AUTO_INCREMENT PRIMARY KEY,
//            player CHAR(31),
//            ip VARCHAR(63),
//            authed TIBYINT(1),
//            source CHAR(31) REFERENCES servers(sid),
//            target CHAR(31) REFERENCES servers(sid),
//            updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
//        )");
    }

    // update this server row in mysql
    public function updateMe()
    {
//        $this->getLogger()->critical("Update me Task ");
        $this::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask($this::getInstance()->getCredentials(),
                "INSERT INTO servers (sid, type, id, ip, port, status, online, max, laston) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE online = ?, laston=CURRENT_TIMESTAMP", [
                ["s", $this::getInstance()->m_ServerUUID],
                ["s", $this->m_ServerType],
                ["i", $this->m_ServerId],
                ["s", $this->m_Mysql->escape_string($this->config->getNested("external_ip"))],
                ["i", $this::getInstance()->getServer()->getPort()],
                ["s", "test"],
                ["i", count($this::getInstance()->getServer()->getOnlinePlayers())],
                ["i", $this::getInstance()->getServer()->getMaxPlayers()],
                ["i", "CURRENT_TIMESTAMP"],
                ["i", count($this::getInstance()->getServer()->getOnlinePlayers())]
            ]
        ));
    }

    // update this server row in mysql
    public function deleteMe()
    {
        MysqlResult::executeQuery($this::getInstance()->connectMainThreadMysql(), "DELETE FROM servers WHERE sid=?", [
            ["s", $this::getInstance()->m_ServerUUID]
                ]
        );
    }

    // get best online
    public function getBest($type = "lobby")
    {
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT * FROM servers WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston) < 5000 AND sid != ? AND `max` > `online` AND `type` = ? ORDER BY `max` DESC LIMIT 1", [
                ["s", $this::getInstance()->m_ServerUUID],
                ["s", $type]
            ]
        );
        if (($result instanceof MysqlSelectResult) and isset($result->rows[0]))
        {
            $server["sid"] = $result->rows[0]["sid"];
            $server["type"] = $result->rows[0]["type"];
            $server["id"] = $result->rows[0]["id"];
            $server["ip"] = $result->rows[0]["ip"];
            $server["port"] = $result->rows[0]["port"];
            $server["status"] = $result->rows[0]["status"];
            $server["online"] = $result->rows[0]["online"];
            $server["max"] = $result->rows[0]["max"];
            $server["diff"] = 0;
            return $server;
        }
        return null;
    }

    // get online servers list
    public function getOthers()
    {
//        $this->getLogger()->critical("Get servers Task");
        $l_Servers = array();
        $l_TotalPlayers = 0;
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT * FROM servers WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston) < 5000 AND sid != ?", [
                ["s", $this::getInstance()->m_ServerUUID]
        ]);
//            var_dump($result);
        if (($result instanceof MysqlSelectResult) and count($result->rows) > 0)
        {
            foreach ($result->rows as $row)
            {
                $server["sid"] = $row["sid"];
                $server["type"] = $row["type"];
                $server["id"] = $row["id"];
                $server["ip"] = $row["ip"];
                $server["port"] = $row["port"];
                $server["status"] = $result->rows[0]["status"];
                $server["online"] = $row["online"];
                $server["max"] = $row["max"];
                $server["diff"] = 0;

                $l_Servers[$server["type"]][$server["id"]] = $server;
                $l_TotalPlayers = $row["online"];
            }
        }
        $this->m_Servers = $l_Servers;
        $this->m_TotalPlayers = $l_TotalPlayers;
    }

    // clean old servers unix_timestamp()-unix_timestamp(laston) > 10
    public function cleanOrphaned()
    {
//        $this->getLogger()->critical("Clean orphaned servers task");
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT * FROM servers WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston) > 10000 AND sid != ?", [
                ["s", $this::getInstance()->m_ServerUUID]
        ]);
        if (($result instanceof MysqlSelectResult) and count($result->rows) > 0)
        {
            foreach ($result->rows as $row)
            {
                $this->getLogger()->info('Orphaned server : ' . $row["type"] . '-' . $row["id"] . ' players : ' . $row["online"] . '-' . $row["max"]);
                MysqlResult::executeQuery($this::getInstance()->connectMainThreadMysql(), "DELETE FROM servers WHERE sid = ?", [
                    ["s", $row['sid']]
                        ]
                );
            }
        }
    }

    public function getTranslatedMessage($key, $value = ["%1", "%2"])
    {
        if ($this->m_Langs->exists($key)) {
            return str_replace(["%1", "%2"], [$value[0], $value[1]], $this->m_Langs->get($key));
        } else {
            return "Language with key \"$key\" does not exist";
        }
    }

    public function transferPlayer(Player $p_Player, string $p_Ip, int $p_Port, string $p_Message)
    {
//        $p_Packet = new TransferPacket;
//        $p_Packet->address = $p_Ip;
//        $p_Packet->m_Port = $p_Port;
        if ($p_Player->loggedIn)
        {
            $p_Player->sendMessage($p_Message);
        }
        $this->getLogger()->info($p_Message . " " . $p_Player->getName() . "  to " . $p_Ip . ":" . $p_Port . "");
//        $p_Player->dataPacket($p_Packet);
        $p_Player->transfer($p_Ip, $p_Port, $p_Message);
    }

    /**
     * @param QueryRegenerateEvent $event
     *
     * @priority LOW
     */
    public function onServerPing(QueryRegenerateEvent $event)
    {
        $event->setMaxPlayerCount($this->config->getNested("network.max"));

        if ($this->config->getNested("network.online") == "total")
        {
            $event->setPlayerCount($this->m_TotalPlayers);
        }
    }

    public function onPlayerJoinEvent(PlayerJoinEvent $p_Event)
    {
        if (!$this->config->getNested("redirect.to_type"))
        {
            return;
        }
        if (count($this->getServer()->getOnlinePlayers()) < $this->config->getNested("redirect.limit"))
        {
            return;
        }

        $player = $p_Event->getPlayer();
        // select random server
        $server = $this->getBest($this->config->getNested("redirect.to_type"));
        // fire event
        $this->getServer()->getPluginManager()->callEvent($l_Event = new BalancePlayerEvent($this, $player, $server["ip"], $server["port"]));
        if ($l_Event->getIp() === null or $l_Event->getPort() === null)
        {
            $player->kick("%disconnectScreen.serverFull", false);
        } else
        {
            $this->transferPlayer($player, $l_Event->getIp(), $l_Event->getPort(), "redirect" /*$this->config->getNested("redirect.message")*/);
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $param): bool
    {
        if ($cmd->getName() === "servers")
        {
            if (count($param) >= 1)
            {
                switch ($param[0])
                {
                    case "list":    // /servers list
                        $sender->sendMessage('This server : ' . $this->config->getNested("node.type") . '-' . $this->config->getNested("node.id") . ' players : ' . count($this::getInstance()->getServer()->getOnlinePlayers()) . ' / ' . $this::getInstance()->getServer()->getMaxPlayers());
                        if (count($this->m_Servers) > 0)
                        {
                            foreach ($this->m_Servers as $l_Types)
                            {
                                foreach ($l_Types as $server)
                                {
                                    $sender->sendMessage('Other server : ' . $server["type"] . '-' . $server["id"] . ' players : ' . $server["online"] . '-' . $server["max"]);
                                }
                            }
                        }
                        break;
                    case "connect":    // /servers connect lobby 1
                        if (count($param) == 3 && $sender instanceof Player)
                        {
                            $server = $this->m_Servers[$param[1]][$param[2]];
                            if (isset($server))
                            {
                                $this->transferPlayer($sender, $server["ip"], $server["port"], "Transfering to " . $server["type"] . "-" . $server["id"]);
                            }
                        }
                        else if (count($param) == 4) // /server connect lobby 1 <pseudo>
                        {
                            $l_Player = $this->getServer()->getPlayer($param[3]);
                            $server = $this->m_Servers[$param[1]][$param[2]];
                            if (isset($server))
                            {
                                $this->transferPlayer($l_Player, $server["ip"], $server["port"], "Transfering to " . $server["type"] . "-" . $server["id"]);
                            }
                        }
                        break;
                    case "test":    // /servers connect lobby 1
//                    if ($sender instanceof Player)
//                    {
//                        $server = $this->getBest();
//                        $this->transferPlayer($sender, $server["ip"], $server["port"], "Transfering to " . $server["type"] . "-" . $server["id"]);
                            var_dump($this->m_Servers);
//                    }
                    break;
                    default:
                        // send help
                        break;
                }
            }
        }
        return true;
    }

}
