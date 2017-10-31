<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 13/09/2017
 * Time: 16:07
 */

namespace fatutils\tools;


use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use ReflectionObject;

class Sidebar
{
	private $m_SidebarParts = [];
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
	private $m_SidebarFormat = TextFormat::RESET . TextFormat::WHITE . "                                                                   %s" . TextFormat::RESET . TextFormat::WHITE;

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

	public function add($p_Line): SidebarPart
	{
		$l_SidebarPart = new SidebarPart($p_Line);
		$this->m_SidebarParts[] = $l_SidebarPart;

		if ($p_Line instanceof DisplayableTimer)
		{
			$p_Line->addTickCallback(function () use ($l_SidebarPart)
				{
					if (FatUtils::getInstance()->getServer()->getTick() % 5 == 0)
						$l_SidebarPart->updateCache();
				});
		}

		return $l_SidebarPart;
	}

	public function addLine(string $p_Line): Sidebar
	{
		$this->add($p_Line);
		return $this;
	}

	public function addTimer(DisplayableTimer &$p_Line): Sidebar
	{
		$this->add($p_Line);
		return $this;
	}

	public function addTranslatedLine(TextFormatter $p_TextFormatter): Sidebar
	{
		$this->add($p_TextFormatter);
		return $this;
	}

	public function addMutableLine(Callable $p_GetLine): Sidebar
	{
		$this->add($p_GetLine);
		return $this;
	}

	public function addWhiteSpace(): Sidebar
	{
		$this->add("");
		return $this;
	}

	public function clearLines(): Sidebar
	{
		$this->m_SidebarParts = [];
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
		foreach (PlayersManager::getInstance()->getFatPlayers() as $l_FatPlayer)
		{
			if ($l_FatPlayer instanceof FatPlayer)
				$this->updatePlayerLines($l_FatPlayer->getPlayer());
		}
	}

	private function updatePlayerLines(Player $p_Player)
	{
		foreach ($this->m_SidebarParts as $l_LineGetter)
		{
			if ($l_LineGetter instanceof SidebarPart)
				$l_LineGetter->updateCacheForPlayer($p_Player, false);
		}

		$this->_updatePlayerCache($p_Player);
	}

	public function _updatePlayerCache(Player $p_Player)
	{
		$l_Ret = [];

		foreach ($this->m_SidebarParts as $l_LineGetter)
		{
			if ($l_LineGetter instanceof SidebarPart)
				$l_Ret = array_merge($l_Ret, explode("\n", $l_LineGetter->_getCacheForPlayer($p_Player)));
		}

		$l_Ret = $this->addSpaces($l_Ret);
		$this->m_LineCache[$p_Player->getUniqueId()->toString()] = implode("\n", $l_Ret);
	}

	public function _display()
	{
		foreach (PlayersManager::getInstance()->getFatPlayers() as $l_FatPlayer)
		{
			if ($l_FatPlayer instanceof FatPlayer)
				$this->_displayForPlayer($l_FatPlayer->getPlayer());
		}
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

	public function isEnabled(): bool
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
