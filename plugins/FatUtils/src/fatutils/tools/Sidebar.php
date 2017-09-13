<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 13/09/2017
 * Time: 16:07
 */

namespace fatutils\tools;


use fatutils\FatUtils;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;
use ReflectionObject;

class Sidebar
{
    private $m_LineGetters = [];
    private $m_LineCache = [];
    private $m_TaskId;

    private $m_Spaces = "                                                         ";

    private static $m_Instance = null;

    public static function getInstance()
    {
        if (!isset(self::$m_Instance))
            self::$m_Instance = new Sidebar();

        return self::$m_Instance;
    }

    /**
     * Sidebar constructor.
     */
    private function __construct()
    {
        $this->m_TaskId = FatUtils::getInstance()->getServer()->getScheduler()->scheduleRepeatingTask(new class(FatUtils::getInstance(), $this) extends PluginTask
        {
            private $m_SidebarInstance;

            /**
             *  constructor.
             * @param Plugin $p_Owner
             * @param Sidebar $p_Instance
             */
            public function __construct(Plugin $p_Owner, Sidebar $p_Instance)
            {
                parent::__construct($p_Owner);
                $this->m_SidebarInstance = $p_Instance;
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
                if ($currentTick % 20 == 0)
                    $this->m_SidebarInstance->_display();
            }
        }, 1);
    }

    //------------------
    // UTILS
    //------------------
    public function addLine(string $p_Line): Sidebar
    {
        $this->m_LineGetters[] = $p_Line;
        return $this;
    }

    public function addMutableLine(Callable $p_GetLine): Sidebar
    {
        $this->m_LineGetters[] = $p_GetLine;
        return $this;
    }

    public function addWhiteSpace(): Sidebar
    {
        $this->addLine("");
        return $this;
    }

    public function clearLines(): Sidebar
    {
        $this->m_LineGetters = [];
        return $this;
    }

    public function update(): Sidebar
    {
        $this->updatePlayersLines();
        $this->_display();
        return $this;
    }

    public function destroy()
    {
        if (isset($this->m_TaskId))
            $this->m_TaskId->cancel();
    }

    //------------------
    // INTERNAL UTILS
    //------------------
    public function updatePlayer(Player $p_Player): Sidebar
    {
        $this->updatePlayerLines($p_Player);
        $this->_displayForPlayer($p_Player);
        return $this;
    }

    private function updatePlayersLines()
    {
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $this->updatePlayerLines($l_Player);
    }

    private function updatePlayerLines(Player $p_Player)
    {
        $l_Ret = [];

        foreach ($this->m_LineGetters as $l_LineGetter)
        {
            if (is_callable($l_LineGetter))
            {
                $l_LineGetterRet = null;

                $params = (new ReflectionObject($l_LineGetter))->getMethod('__invoke')->getParameters();
                if (count($params) == 0)
                    $l_LineGetterRet = $l_LineGetter();
                else if (count($params) == 1)
                    $l_LineGetterRet = $l_LineGetter($p_Player);

                switch (gettype($l_LineGetterRet))
                {
                    case 'array':
                        foreach ($l_LineGetterRet as $l_Line)
                            $l_Ret[] = $l_Line;
                        break;
                    case 'string':
                        foreach (explode("\n", $l_LineGetterRet) as $l_Line)
                            $l_Ret[] = $l_Line;
                        break;
                }
            } else if (gettype($l_LineGetter) === 'string')
            {
                foreach (explode("\n", $l_LineGetter) as $l_Line)
                    $l_Ret[] = $l_Line;
            }
        }

        $l_Ret = $this->addSpaces($l_Ret);
        $this->m_LineCache[$p_Player->getUniqueId()->toBinary()] = implode("\n", $l_Ret);
    }

    public function _display()
    {
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $this->_displayForPlayer($l_Player);
    }

    public function _displayForPlayer(Player $p_Player)
    {
        if (!isset($this->m_LineCache[$p_Player->getUniqueId()->toBinary()]))
            $this->updatePlayerLines($p_Player);

        $p_Content = $this->m_LineCache[$p_Player->getUniqueId()->toBinary()];
        $p_Player->sendPopup("", $p_Content);
    }

    private function addSpaces(array $p_Lines): array
    {
        for ($i = 0, $l = count($p_Lines); $i < $l; $i++)
            $p_Lines[$i] = $this->m_Spaces . $p_Lines[$i];

        return $p_Lines;
    }
}