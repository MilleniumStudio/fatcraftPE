<?php

namespace fatcraft\loadbalancer;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
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
    private $m_ServerState = "closed"; // open / closed

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

        //test hack
        $this->setServerState("open");

        // update my status every second
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new class($this) extends PluginTask
        {
            public function onRun(int $currentTick)
            {
                LoadBalancer::getInstance()->updateMe();
            }
        }, 0, $this->config->getNested("timers.self"));

        //update other server status every seconds too
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new class($this) extends PluginTask
        {
            public function onRun(int $currentTick)
            {
                LoadBalancer::getInstance()->getOthers();
            }
        }, 0, $this->config->getNested("timers.others"));

        //Clean orphaned servers
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new class($this) extends PluginTask
        {
            public function onRun(int $currentTick)
            {
                LoadBalancer::getInstance()->cleanOrphaned();
            }
        }, 0, $this->config->getNested("timers.cleaner"));
        $this->getLogger()->info("Enabled");
    }

    public function onDisable()
    {
        // select random server
        $server = $this->getBest($this->config->getNested("redirect.to_type"));
        if ($server != false)
        {
            foreach ($this->getServer()->getLoggedInPlayers() as $l_Player)
            {
                // fire event
                $this->getServer()->getPluginManager()->callEvent($l_Event = new BalancePlayerEvent($this, $l_Player, $server["ip"], $server["port"]));
                if ($l_Event->getIp() === null or $l_Event->getPort() === null)
                {
                    $l_Player->kick("%disconnectScreen.restarting", false);
                } else
                {
                    $this->transferPlayer($l_Event->getPlayer(), $l_Event->getIp(), $l_Event->getPort(), $this->config->getNested("redirect.message"));
                }
            }
        }

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

    public function getServerState()
    {
        return $this->m_ServerState;
    }

    public function setServerState(String $p_State)
    {
        $this->m_ServerState = $p_State;
    }

    public function getPlayerServerUUIDByName(String $p_PlayerName)
    {
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT * FROM players_on_servers WHERE name = ?", [
                ["s", $p_PlayerName]
        ]);
        if (($result instanceof MysqlSelectResult) and count($result->rows) == 1)
        {
            return $result->rows[0]["sid"];
        }
        return null;
    }

    public function getPlayerServerUUIDByUUID(String $p_PlayerUUID)
    {
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT * FROM players_on_servers WHERE uuid = ?", [
                ["s", $p_PlayerUUID]
        ]);
        if (($result instanceof MysqlSelectResult) and count($result->rows) == 1)
        {
            return $result->rows[0]["sid"];
        }
        return null;
    }

    public function getServerData(String $p_ServerUUID)
    {
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT * FROM servers WHERE sid = ?", [
                ["s", $p_ServerUUID]
        ]);
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
            $server["diff"] = $result->rows[0]["diff"];
            return $server;
        }
        return null;
    }

    private function initDatabase()
    {
        $this->m_Mysql->query("CREATE TABLE IF NOT EXISTS servers (
            sid CHAR(36) PRIMARY KEY,
            type VARCHAR(20),
            id INT(11),
            ip VARCHAR(15),
            port SMALLINT,
            status VARCHAR(63) DEFAULT closed,
            online SMALLINT,
            max SMALLINT,
            laston TIMESTAMP default CURRENT_TIMESTAMP
        )");

        $this->m_Mysql->query("CREATE TABLE IF NOT EXISTS players_on_servers (
            name CHAR(50),
            uuid CHAR(36) PRIMARY KEY,
            sid CHAR(36),
            ip VARCHAR(63),
            updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    // update this server row in mysql
    public function updateMe()
    {
//        $this->getLogger()->critical("Update me Task ");
        $this::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask($this::getInstance()->getCredentials(),
                "INSERT INTO servers (sid, type, id, ip, port, status, online, max) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE online = ?, laston=CURRENT_TIMESTAMP", [
                ["s", $this::getInstance()->m_ServerUUID],
                ["s", $this->m_ServerType],
                ["i", $this->m_ServerId],
                ["s", $this->m_Mysql->escape_string($this->config->getNested("external_ip"))],
                ["i", $this::getInstance()->getServer()->getPort()],
                ["s", $this->m_ServerState],
                ["i", count($this::getInstance()->getServer()->getOnlinePlayers())],
                ["i", $this::getInstance()->getServer()->getMaxPlayers()],
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
        //delete players
        MysqlResult::executeQuery($this::getInstance()->connectMainThreadMysql(), "DELETE FROM players_on_servers WHERE sid=?", [
            ["s", $this::getInstance()->m_ServerUUID]
        ]
        );
    }

    // get best online
    public function getBest($type = "lobby", $p_State = "open")
    {
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT *, (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston)) AS diff  FROM servers WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston) < 5 AND sid != ? AND `max` > `online` AND `type` = ? AND `status` = ? ORDER BY `max` DESC LIMIT 1", [
                ["s", $this::getInstance()->m_ServerUUID],
                ["s", $type],
                ["s", $p_State]
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
            $server["diff"] = $result->rows[0]["diff"];
            return $server;
        }
        return null;
    }

    // get online servers list
    public function getOthers()
    {
        $l_Servers = array();
        $l_TotalPlayers = 0;
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT *, (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston)) AS diff  FROM servers WHERE (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston)) < 5 AND sid != ?", [
                ["s", $this::getInstance()->m_ServerUUID]
        ]);
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
                $server["diff"] = $row["diff"];

                $l_Servers[$server["type"]][$server["id"]] = $server;
                $l_TotalPlayers = $row["online"];
            }
        }
        $this->m_Servers = $l_Servers;
        $this->m_TotalPlayers = $l_TotalPlayers;
    }

    public function getServers($type = "lobby", $p_State = "open")
    {
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT *, (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston)) AS diff FROM servers WHERE `type` = ? AND `status` = ?", [
                ["s", $type],
                ["s", $p_State]
            ]
        );
        if (($result instanceof MysqlSelectResult) and count($result->rows) > 0)
        {
            $server = array();
            foreach ($result->rows as $row)
            {
                $server[]["sid"] = $row->rows[0]["sid"];
                $server[]["type"] = $row->rows[0]["type"];
                $server[]["id"] = $row->rows[0]["id"];
                $server[]["ip"] = $row->rows[0]["ip"];
                $server[]["port"] = $row->rows[0]["port"];
                $server[]["status"] = $row->rows[0]["status"];
                $server[]["online"] = $row->rows[0]["online"];
                $server[]["max"] = $row->rows[0]["max"];
                $server[]["diff"] = $row->rows[0]["diff"];
            }
            return $server;
        }
        return null;
    }

    // clean old servers unix_timestamp()-unix_timestamp(laston) > 10
    public function cleanOrphaned()
    {
//        $this->getLogger()->critical("Clean orphaned servers task");
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT * FROM servers WHERE (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(laston)) > ? AND sid != ?", [
                ["i", $this->config->getNested("timers.timeout")],
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
                MysqlResult::executeQuery($this::getInstance()->connectMainThreadMysql(), "DELETE FROM players_on_servers WHERE sid = ?", [
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
        $p_Player->sendMessage($p_Message);
        $this->getLogger()->info($p_Message . " " . $p_Player->getName() . " to " . $p_Ip . ":" . $p_Port . "");

        $this->getServer()->getPluginManager()->callEvent($ev = new PlayerTransferEvent($p_Player, $p_Ip, $p_Port, $p_Message));

        if(!$ev->isCancelled())
        {
            // TODO insert in Transfert table
            $pk = new TransferPacket();
            $pk->address = $ev->getAddress();
            $pk->port = $ev->getPort();
            $p_Player->directDataPacket($pk, true);
//            $p_Player->close("", $ev->getMessage(), false);
        }
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

    /**
     * @param PlayerJoinEvent $p_Event
     *
     * @priority HIGH
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $p_Event)
    {
        if ($this->isPlayerConnected($p_Event->getPlayer()->getName() && $this->config->getNested("players.singlesession") == "true"))
        {
            $p_Event->getPlayer()->kick("You are already connected !", false);
        }
        else
        {
            $p_Event->setJoinMessage("");
            if ($this->config->getNested("redirect.to_type") != false && count($this->getServer()->getOnlinePlayers()) > $this->config->getNested("redirect.limit"))
            {
                try
                {
                    // select random server
                    $server = $this->getBest($this->config->getNested("redirect.to_type"), "open");
                    if ($server !== null)
                    {
                        // fire event
                        $this->getServer()->getPluginManager()->callEvent($l_Event = new BalancePlayerEvent($this, $p_Event->getPlayer(), $server["ip"], $server["port"]));
                        if ($l_Event->getIp() === null or $l_Event->getPort() === null)
                        {
                            $p_Event->getPlayer()->kick("%disconnectScreen.serverFull", false);
                        }
                        else
                        {
                            $this->transferPlayer($p_Event->getPlayer(), $l_Event->getIp(), $l_Event->getPort(), $this->config->getNested("redirect.message"));
                        }
                    }
                    else
                    {
                        $p_Event->getPlayer()->kick("LoadBalancer error, no server route !", false);
                    }
                }
                catch (Exception $ex)
                {
                    $p_Event->getPlayer()->kick("Problem occured on LoadBalancer !", false);
                }
            }
            else
            {
                // TODO check Transfert table
                $this->insertPlayer($p_Event->getPlayer());
            }
        }
    }

    public function insertPlayer(Player $p_Player)
    {
        $this::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask($this::getInstance()->getCredentials(),
                "INSERT INTO players_on_servers (name, uuid, sid, ip) VALUES (?, ?, ?, ?)", [
                ["s", $p_Player->getName()],
                ["s", $p_Player->getUniqueId()->toString()],
                ["s", $this->getServer()->getServerUniqueId()->toString()],
                ["s", $p_Player->getAddress()]
            ]
        ));
    }

    public function isPlayerConnected(String $p_Name) : Bool
    {
        $result = MysqlResult::executeQuery($this->connectMainThreadMysql(),
            "SELECT * FROM players_on_servers WHERE sid != ?", [
                ["s", $this::getInstance()->m_ServerUUID]
        ]);
        if (($result instanceof MysqlSelectResult) and count($result->rows) == 1)
        {
            return true;
        }
        return false;
    }

    public function onPlayerQuitEvent(PlayerQuitEvent $p_Event)
    {
        $p_Event->setQuitMessage("");
        $this->removePlayerPlayer($p_Event->getPlayer());
    }

    public function removePlayerPlayer(Player $p_Player)
    {
        $this::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask($this::getInstance()->getCredentials(),
                "DELETE FROM players_on_servers WHERE name = ?", [
                ["s", $p_Player->getName()]
            ]
        ));
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $p_Param): bool
    {
        if ($cmd->getName() === "server")
        {
            if (count($p_Param) >= 1)
            {
                switch ($p_Param[0])
                {
                    case "list":    // /server list
                        $sender->sendMessage('This server : ' . $this->config->getNested("node.type") . '-' . $this->config->getNested("node.id") . ' players : ' . count($this::getInstance()->getServer()->getOnlinePlayers()) . ' / ' . $this::getInstance()->getServer()->getMaxPlayers());
                        if (count($this->m_Servers) > 0)
                        {
                            if (count($p_Param) == 1) // /server list
                            {
                                foreach ($this->m_Servers as $l_Type)
                                {
//                                    $sender->sendMessage('Servers ' . $l_Type[0]["type"] . ':');
                                    foreach ($l_Type as $l_Server)
                                    {
                                        $sender->sendMessage(' - ' . $l_Server["type"] . '-' . $l_Server["id"] . ' ' . $l_Server["online"] . '/' . $l_Server["max"]);
                                    }
                                }
                            }
                            elseif (count($p_Param) == 2) // /server list <template>
                            {
                                $l_Type = $p_Param[1];
                                $sender->sendMessage('Servers ' . $l_Type . ':');
                                foreach ($this->m_Servers[$l_Type] as $l_Server)
                                {
                                    $sender->sendMessage(' - ' . $l_Server["type"] . '-' . $l_Server["id"] . ' ' . $l_Server["online"] . '/' . $l_Server["max"]);
                                }
                            }
                        }
                        else
                        {
                            $sender->sendMessage('No other server online !');
                        }
                        break;
                    case "connect":
                        if (count($p_Param) >= 3) // /server connect <player> <template> [id]
                        {
                            $l_Player = $this->getServer()->getPlayer($p_Param[1]);
                            if ($l_Player !== null)
                            {
                                $l_Template = $p_Param[2];
                                if (count($p_Param) == 3)   // /server connect <player> lobby
                                {
                                    $l_Server = $this->getBest($l_Template);
                                    if (isset($l_Server))
                                    {
                                        $this->transferPlayer($l_Player, $l_Server["ip"], $l_Server["port"], "Transfering to " . $l_Server["type"] . "-" . $l_Server["id"]);
                                    }
                                }
                                else if (count($p_Param) == 4) // /server connect <player> lobby 1
                                {
                                    $l_Id = $p_Param[3];
                                    $l_Server = $this->m_Servers[$l_Template][$l_Id];
                                    if (isset($l_Server))
                                    {
                                        $this->transferPlayer($l_Player, $l_Server["ip"], $l_Server["port"], "Transfering to " . $l_Server["type"] . "-" . $l_Server["id"]);
                                    }
                                }
                                else
                                {
                                    $this->sendServerHelp($sender);
                                }
                            }
                            else
                            {
                                $sender->sendMessage('Unknown player ' . $p_Param[1]);
                            }
                        }
                        break;
                    case "test":
                        var_dump($this->m_Servers);
                    break;
                    default:
                        $this->sendServerHelp($sender);
                        break;
                }
            }
        }
        return true;
    }

    private function sendServerHelp(CommandSender $sender)
    {
        $sender->sendMessage("Servers help :");
        $sender->sendMessage(" /server list [template]");
        $sender->sendMessage(" /server connect <player> <template> [id]");
    }

}
