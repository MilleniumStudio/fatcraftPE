<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 14:17
 */

namespace hungergames;


use fatutils\players\PlayersManager;
use fatutils\tools\WorldUtils;
use pocketmine\utils\Config;

class HungerGameConfig
{
	private $m_Chests = [];
	private $m_IsSkyWars = false;

	/**
	 * HungerGameConfig constructor.
	 * @param Config $p_Config
	 */
	public function __construct(Config $p_Config)
	{
		$this->m_IsSkyWars = $p_Config->get("isSkywar");

		foreach ($p_Config->get("chests") as $l_RawLocation)
			$this->m_Chests[] = WorldUtils::stringToLocation($l_RawLocation);
	}

	/**
	 * @return array
	 */
	public function getChests():array
	{
		return $this->m_Chests;
	}

	/**
	 * @return bool|mixed
	 */
	public function isSkyWars():boolean
	{
		return $this->m_IsSkyWars;
	}


}