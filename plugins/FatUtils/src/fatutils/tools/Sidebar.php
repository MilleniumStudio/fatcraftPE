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
use pocketmine\utils\TextFormat;
use ReflectionObject;

class Sidebar
{
    private $m_LineGetters = [];
    private $m_LineCache = [];
    private $m_TaskId;
    private $m_DisplayTickInterval = 20;

    private $m_Enabled = true;

    // -1 means no automatic update
    private $m_UpdateTickInterval = -1;

    /**
     * This is the formater for the sidebar,
     *  the sidebar is base on the Player::sendPopup() function which
     *  while display lines in the center bottom of the screen.
     *
     * So if you want your sidebar to the screen left, use "%s                                                               ",
     *  or in the center use "%s".
     *  Default is screen right.
     */
    private $m_SidebarFormat = TextFormat::RESET . TextFormat::WHITE . "                                                                   %s". TextFormat::RESET . TextFormat::WHITE;

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
                if ($this->m_SidebarInstance->isEnabled())
                {
                    if ($currentTick % $this->m_SidebarInstance->getDisplayTickInterval() == 0)
                        $this->m_SidebarInstance->_display();
                    if ($this->m_SidebarInstance->getUpdateTickInterval() >= 0 && $currentTick % $this->m_SidebarInstance->getUpdateTickInterval() == 0)
                        $this->m_SidebarInstance->update();
                }
            }
        }, 1);
    }

    //------------------
    // UTILS
    //------------------
    public function setFormat(string $p_Format): Sidebar
    {
        $this->m_SidebarFormat = $p_Format;
        return $this;
    }

    public function addLine(string $p_Line): Sidebar
    {
        $this->m_LineGetters[] = $p_Line;
        return $this;
    }

    public function addTranslatedLine(TextFormatter $p_TextFormatter): Sidebar
    {
        $this->m_LineGetters[] = $p_TextFormatter;
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

    public function enable()
    {
        $this->m_Enabled = true;
    }

    public function disable()
    {
        $this->m_Enabled = false;
    }

    // preferably use disable()
    public function destroy()
    {
        if (isset($this->m_TaskId))
            $this->m_TaskId->cancel();
    }

    // only use positive values (advised value 20 (cause no difference otherwise...))
    public function setDisplayTickInterval(int $m_DisplayTickInterval): Sidebar
    {
        $this->m_DisplayTickInterval = $m_DisplayTickInterval;
        return $this;
    }

    /**
     * -1 means no automatic update
     * prefer high values, update can be costly...
     *
     * @param int $m_UpdateTickInterval
     * @return Sidebar
     */
    public function setUpdateTickInterval(int $m_UpdateTickInterval): Sidebar
    {
        $this->m_UpdateTickInterval = $m_UpdateTickInterval;
        return $this;
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

    private function lineSplitter(string $p_line):array
    {
        return explode("\n", $p_line);
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

                if ($l_LineGetterRet instanceof TextFormatter)
                    $l_LineGetterRet = $l_LineGetterRet->asStringForPlayer($p_Player);

                switch (gettype($l_LineGetterRet))
                {
                    case 'array':
                        foreach ($l_LineGetterRet as $l_Line)
                        {
                            if ($l_Line instanceof TextFormatter)
                                $l_Line = $l_Line->asStringForPlayer($p_Player);

                            $l_Ret[] = $l_Line;
                        }
                        break;
                    case 'string':
                        foreach ($this->lineSplitter($l_LineGetterRet) as $l_Line)
                            $l_Ret[] = $l_Line;
                        break;
                }
            } else if (gettype($l_LineGetter) === 'string')
            {
                foreach ($this->lineSplitter($l_LineGetter) as $l_Line)
                    $l_Ret[] = $l_Line;
            } else if ($l_LineGetter instanceof TextFormatter)
            {
                foreach ($this->lineSplitter($l_LineGetter->asStringForPlayer($p_Player)) as $l_Line)
                    $l_Ret[] = $l_Line;
            }
        }

        $l_Ret = $this->addSpaces($l_Ret);
        $this->m_LineCache[$p_Player->getUniqueId()->toString()] = implode("\n", $l_Ret);
    }

    public function _display()
    {
        foreach (FatUtils::getInstance()->getServer()->getOnlinePlayers() as $l_Player)
            $this->_displayForPlayer($l_Player);
    }

    public function _displayForPlayer(Player $p_Player)
    {
        if (!isset($this->m_LineCache[$p_Player->getUniqueId()->toString()]))
            $this->updatePlayerLines($p_Player);

        $p_Content = $this->m_LineCache[$p_Player->getUniqueId()->toString()];
        $p_Player->sendPopup($p_Content, "");
    }

    private function addSpaces(array $p_Lines): array
    {
        for ($i = 0, $l = count($p_Lines); $i < $l; $i++)
            $p_Lines[$i] = sprintf($this->m_SidebarFormat, $p_Lines[$i]);

        return $p_Lines;
    }

    /**
     * @return int
     */
    public function getDisplayTickInterval(): int
    {
        return $this->m_DisplayTickInterval;
    }

    public function isEnabled():bool
    {
        return (bool)$this->m_Enabled;
    }

    /**
     * @return int
     */
    public function getUpdateTickInterval(): int
    {
        return $this->m_UpdateTickInterval;
    }
}
