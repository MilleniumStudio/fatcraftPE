<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 30/10/2017
 * Time: 17:12
 */

namespace fatutils\tools;


use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use pocketmine\Player;
use ReflectionObject;

class SidebarPart
{
	private $m_LineGetter = null;

	/**
	 * @var array PlayerUUID => string
	 */
	private $m_Cache = [];

	/**
	 * SidebarPart constructor.
	 * @param null $p_LineGetter
	 */
	public function __construct($p_LineGetter)
	{
		$this->setLineGetter($p_LineGetter);
	}

	public function updateCache()
	{
		foreach (PlayersManager::getInstance()->getFatPlayers() as $l_FatPlayer)
		{
			if ($l_FatPlayer instanceof FatPlayer)
				$this->updateCacheForPlayer($l_FatPlayer->getPlayer());
		}
	}

	public function updateCacheForPlayer(Player $p_Player, bool $p_UpdateSidebarCache = true)
	{
		$l_Ret = [];
		$l_LineGetter = $this->m_LineGetter;

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
		} else if ($l_LineGetter instanceof DisplayableTimer)
		{
			$l_Ret[] = $l_LineGetter->toString($p_Player);
		}

		$this->m_Cache[$p_Player->getUniqueId()->toString()] = implode("\n", $l_Ret);

		if ($p_UpdateSidebarCache)
			Sidebar::getInstance()->_updatePlayerCache($p_Player);
	}

	private function lineSplitter(string $p_line):array
	{
		return explode("\n", $p_line);
	}

	/**
	 * @param null $m_LineGetter
	 */
	public function setLineGetter($m_LineGetter)
	{
		$this->m_LineGetter = $m_LineGetter;
	}

	public function _getCacheForPlayer(Player $p_Player)
	{
		$l_Key = $p_Player->getUniqueId()->toString();
		if (array_key_exists($l_Key, $this->m_Cache))
			return $this->m_Cache[$l_Key];
		else
			return "";
	}
}