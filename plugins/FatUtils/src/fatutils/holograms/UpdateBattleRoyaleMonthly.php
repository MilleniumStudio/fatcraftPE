<?php


namespace fatutils\holograms;


use pocketmine\scheduler\PluginTask;
use fatcraft\loadbalancer\LoadBalancer;
use fatutils\holograms\HologramsManager;
use libasynql\result\MysqlResult;
use libasynql\result\MysqlSelectResult;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

class UpdateBattleRoyaleMonthly extends PluginTask
{
    public function __construct(Plugin $owner)
    {
        parent::__construct($owner);
    }

    public function onRun(int $currentTick)
    {
        $textBuffer = "";
        $result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
            "SELECT players.`name`, COUNT(scores.`position`) AS number FROM scores, players  WHERE players.`uuid` = scores.`player` AND serverType = \"battleRoyale\" && POSITION = 100 && MONTH(scores.`date`) >= MONTH(NOW()) GROUP BY player ORDER BY number DESC LIMIT 20", []
        );
        if (($result instanceof MysqlSelectResult) and isset($result->rows[0]))
        {
            $i = 0;
            while (isset($result->rows[$i]))
            {
                $val = $i + 1;

                if ($i + 1 <=3)
                    $textBuffer .= "§6" . $val . "§5 - " . $result->rows[$i]["name"] . " ->§4 " . $result->rows[$i]["number"] . "\n";
                else
                    $textBuffer .= "§6" . $val . "§r - " . $result->rows[$i]["name"] . " -> " . $result->rows[$i]["number"] . "\n";
                $i++;
            }
        }

        if (HologramsManager::getInstance()->getHologram("Top20BattleRoyale") != null)
        {/**/
            $holo = HologramsManager::getInstance()->getHologram("Top20BattleRoyale");
            if ($holo != null)
                HologramsManager::getInstance()->getHologram("Top20BattleRoyale")->updateTextWithString($textBuffer);
        }
        else
            echo("Top20BattleRoyale hologram not loaded\n");
    }

    public function cancel() {
        $this->getHandler()->cancel();
    }
}
