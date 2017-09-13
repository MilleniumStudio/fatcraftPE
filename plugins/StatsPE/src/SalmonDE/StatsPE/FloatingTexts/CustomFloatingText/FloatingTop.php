<?php
namespace SalmonDE\StatsPE\FloatingTexts\CustomFloatingText;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use SalmonDE\StatsPE\Base;
use SalmonDE\StatsPE\Providers\MySQLProvider;

/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 13/09/2017
 * Time: 11:48
 */
class FloatingTop extends CustomFloatingText
{
    private $query;

    public function __construct(string $statName, int $x, int $y, int $z, level $level, int $nbLines, string $customName=null){
        parent::__construct(new \pocketmine\math\Vector3($x, $y, $z), $level, '', TextFormat::BOLD.TextFormat::LIGHT_PURPLE.($customName==null?$statName:$customName));
        $this->query = "SELECT Username as player, `".$statName."` as val FROM StatsPE ORDER BY `".$statName."` DESC LIMIT 0,".$nbLines;
    }

    public function needUpdate(int $tick): bool
    {
        return $tick % 100 == 0 && $this->query != null;
    }

    public function update()
    {
        $texts = [];
        $mysqlProvider = Base::getInstance()->getDataProvider();
        if($mysqlProvider instanceof MySQLProvider) {
            $result = $mysqlProvider->queryDb($this->query, []);
            if ($result instanceof \mysqli_result) {
                while($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    $texts[] = $row["player"].": ".TextFormat::BOLD.TextFormat::GOLD.$row["val"];
                }
                $this->setText($texts);
            } else {
                echo "error on result";
            }
        }
        else{
            echo "Error: no MySQLProvider available for FloatingTop\n";
        }
    }
}