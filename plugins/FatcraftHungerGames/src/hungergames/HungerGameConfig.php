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
	private $m_IsSkyWars = false;

	/**
	 * HungerGameConfig constructor.
	 * @param Config $p_Config
	 */
	public function __construct(Config $p_Config)
	{
		$this->m_IsSkyWars = $p_Config->get("isSkywar");
	}

	/**
	 * @return bool
	 */
	public function isSkyWars():bool
	{
		return $this->m_IsSkyWars;
	}


}