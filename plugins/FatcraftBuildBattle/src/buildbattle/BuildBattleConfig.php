<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 14:17
 */

namespace buildbattle;


use fatutils\players\PlayersManager;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use fatutils\tools\WorldUtils;
use pocketmine\level\Location;
use pocketmine\utils\Config;

class BuildBattleConfig
{
	const END_GAME_TIMER = "endGameTime";
	private  $m_endGameTimer = 0;

	public function __construct(Config $p_config)
	{
		if ($p_config->exists(BuildBattleConfig::END_GAME_TIMER))
			$this->m_endGameTimer = $p_config->get(BuildBattleConfig::END_GAME_TIMER, 0);
		else
			echo("endGameTime property does not exist in the config.yml\n");
	}

	public function getEndGameTime() : int
	{
		return $this->m_endGameTimer;
	}
}

?>