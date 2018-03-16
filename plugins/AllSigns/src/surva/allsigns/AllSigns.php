<?php
/**
 * Created by PhpStorm.
 * User: surva
 * Date: 14.05.16
 * Time: 12:01
 */

namespace surva\allsigns;

use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\holograms\HologramsManager;
use fatutils\tools\Sidebar;
use fatutils\tools\TextFormatter;
use libasynql\DirectQueryMysqlTask;
use libasynql\result\MysqlResult;
use libasynql\result\MysqlSelectResult;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;
use surva\allsigns\tasks\SignUpdate;
use pocketmine\plugin\PluginBase;
use fatutils\tools\WorldUtils;


class AllSigns extends PluginBase
{
    static $m_Instance = null;

    public static function getInstance()
    {
        if (!isset(self::$m_Instance))
            self::$m_Instance = new AllSigns();

        return self::$m_Instance;
    }

    public $m_timers = [];

    public function onEnable()
    {
		WorldUtils::stopWorldsTime();
		WorldUtils::setWorldsTime(0);
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new SignUpdate($this), 60);

        Sidebar::getInstance()->clearLines();

        Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.pk"))
            ->addTranslatedLine(new TextFormatter("template.playfatcraft"))
            ->addWhiteSpace()
            ->addMutableLine(function (Player $p_Player)
            {
                if (isset($p_Player) && isset($this->m_timers[((string)($p_Player->getXuid()))]))
                {
                    if ($this->m_timers[((string)($p_Player->getXuid()))] != 0)
                    {
                        return new TextFormatter("parkour.time", ["time" => microtime(true) - $this->m_timers[((string)($p_Player->getXuid()))]]);
                    }
                }
            });
        HologramsManager::getInstance();

        FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new UpdateHolograms($this), 100);
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new UpdateSidebar($this), 2);

    }

    public function startTimer(string $playerName)
    {
        $player = LoadBalancer::getInstance()->getServer()->getPlayer($playerName);

        $player->addTitle("§2Go !§r");

        $this->m_timers[$player->getXuid()] = microtime(true);;
    }

    public function validateTime(string $playerName)
    {
        $player = LoadBalancer::getInstance()->getServer()->getPlayer($playerName);

        if ($this->m_timers[$player->getXuid()] == 0)
            return;
        $time = microtime(true);
        $result = $time - $this->m_timers[$player->getXuid()];
        $player->addTitle("§2Finish !§r", "§5You have done : §6" . $result . "§5 seconds !§r");

        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "INSERT INTO chrono_scores (xuid, player_name, map_name,time) VALUES (?, ?, ?, ?)", [
                    ["s", $player->getUniqueId()],
                    ["s", $player->getName()],
                    ["s", "mirrorsEdge"],
                    ["d", $result]
                ]
            ));
        $this->m_timers[$player->getXuid()] = 0;
    }

    public function updateSidebar()
    {
        Sidebar::getInstance()->clearLines();

        Sidebar::getInstance()->addTranslatedLine(new TextFormatter("template.pk"))
            ->addTranslatedLine(new TextFormatter("template.playfatcraft"))
            ->addWhiteSpace()
            ->addMutableLine(function (Player $p_Player)
            {
                if (isset($p_Player) && isset($this->m_timers[((string)($p_Player->getXuid()))]))
                {
                    if ($this->m_timers[((string)($p_Player->getXuid()))] != 0)
                    {
                        return new TextFormatter("parkour.time", ["time" => sprintf("%.2f", microtime(true) - $this->m_timers[((string)($p_Player->getXuid()))])]);
                    }
                }
            });
        Sidebar::getInstance()->update();
    }
}


class UpdateHolograms extends PluginTask
{
    public function __construct(Plugin $owner)
    {
        parent::__construct($owner);
    }

    public function onRun(int $currentTick)
    {
        $textBuffer = "";
        $result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
            "SELECT MIN(`time`) AS `minTime`, `player_name` FROM chrono_scores GROUP BY `player_name` ORDER BY `minTime` ASC LIMIT 20", []
        );
        if (($result instanceof MysqlSelectResult) and isset($result->rows[0]))
        {
            $i = 0;
            while (isset($result->rows[$i]))
            {
                $val = $i + 1;

                $floatResult = sprintf("%.2f", $result->rows[$i]["minTime"]);
                $intVal = intval($floatResult);
                $minute = intVal($floatResult / 60);
                $second = $intVal % 60;
                $cents = ($floatResult - floatval($intVal)) * 100;

                if ($i + 1 <=3)
                    $textBuffer .= "§6" . $val . "§5 - " . $result->rows[$i]["player_name"] . " ->§4 " . $minute . "'" . $second . "\"" . $cents . "\n";
                else
                    $textBuffer .= "§6" . $val . "§r - " . $result->rows[$i]["player_name"] . " -> " . $minute . "'" . $second . "\"" . $cents . "\n";
                $i++;
            }
        }
        //wwwsecho("text buffer = " . $textBuffer. "\n");

        if (HologramsManager::getInstance()->getHologram("Top20MirrorsEdge") != null)
        {
            HologramsManager::getInstance()->getHologram("Top20MirrorsEdge")->updateTextWithString($textBuffer);
            HologramsManager::getInstance()->getHologram("Top20MirrorsEdge2")->updateTextWithString($textBuffer);
        }

        else
            echo("Top20MirrorsEdge hologram not loaded\n");
    }

    public function cancel() {
        $this->getHandler()->cancel();
    }
}

class UpdateSidebar extends PluginTask
{
    public function __construct(Plugin $owner)
    {
        parent::__construct($owner);
    }

    public function onRun(int $currentTick)
    {
        AllSigns::getInstance()->updateSidebar();
    }

    public function cancel() {
        $this->getHandler()->cancel();
    }
}