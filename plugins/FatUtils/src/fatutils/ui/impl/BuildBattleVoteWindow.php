<?php
/**
 * Created by PhpStorm.
 * User: Naphtaline
 * Date: 03/01/2018
 * Time: 11:02
 */

namespace fatutils\ui\impl;

use buildbattle\BuildBattle;
use fatcraft\loadbalancer\LoadBalancer;
use pocketmine\Player;

use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\players\PlayersManager;
use fatutils\players\FatPlayer;

class BuildBattleVoteWindow
{
	public function __construct(Player $p_Player)
	{
		$l_FatPlayer = PlayersManager::getInstance()->getFatPlayer($p_Player);

		$l_Window = new ButtonWindow($p_Player);
		$l_Window->setTitle((new TextFormatter("template.menu.title"))->asStringForPlayer($p_Player));

		$l_Window->addPart((new Button())
			->setText((new TextFormatter("template.bw"))->asStringForPlayer($p_Player))
			->setCallback(function () use ($l_FatPlayer)
			{
				BuildBattle::getInstance()->voteAddCurrentArea(1);
			})
		);


		$l_Window->open();
	}
}