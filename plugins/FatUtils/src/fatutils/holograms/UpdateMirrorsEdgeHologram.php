<?php


namespace fatutils\holograms;


use pocketmine\scheduler\PluginTask;
use fatcraft\loadbalancer\LoadBalancer;
use fatutils\holograms\HologramsManager;
use libasynql\result\MysqlResult;
use libasynql\result\MysqlSelectResult;
use pocketmine\plugin\Plugin;

class UpdateMirrorsEdgeHologram extends PluginTask
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

        if (HologramsManager::getInstance()->getHologram("Top20MirrorsEdge") != null)
        {
            $holo = HologramsManager::getInstance()->getHologram("Top20MirrorsEdge");
            if ($holo != null)
                HologramsManager::getInstance()->getHologram("Top20MirrorsEdge")->updateTextWithString($textBuffer);

            $holo = HologramsManager::getInstance()->getHologram("Top20MirrorsEdge2");
            if ($holo != null)
                HologramsManager::getInstance()->getHologram("Top20MirrorsEdge2")->updateTextWithString($textBuffer);
        }

        else
            echo("Top20MirrorsEdge hologram not loaded\n");
    }

    public function cancel() {
        $this->getHandler()->cancel();
    }
}
