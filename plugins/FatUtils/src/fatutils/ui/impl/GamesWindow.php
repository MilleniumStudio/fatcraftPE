<?php

namespace fatutils\ui\impl;

use fatcraft\loadbalancer\LoadBalancer;
use pocketmine\Player;

use fatutils\tools\TextFormatter;
use fatutils\ui\windows\ButtonWindow;
use fatutils\ui\windows\parts\Button;
use fatutils\players\PlayersManager;
use fatutils\players\FatPlayer;

class GamesWindow
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
                LoadBalancer::getInstance()->balancePlayer($l_FatPlayer->getPlayer(), LoadBalancer::TEMPLATE_TYPE_BEDWAR);
            })
        );

		$l_Window->addPart((new Button())
			->setText((new TextFormatter("template.br"))->asStringForPlayer($p_Player))
			->setCallback(function () use ($l_FatPlayer)
			{
				LoadBalancer::getInstance()->balancePlayer($l_FatPlayer->getPlayer(), LoadBalancer::TEMPLATE_TYPE_BOAT_RACER);
			})
		);

		$l_Window->addPart((new Button())
			->setText((new TextFormatter("template.hg"))->asStringForPlayer($p_Player))
			->setCallback(function () use ($l_FatPlayer)
			{
				LoadBalancer::getInstance()->balancePlayer($l_FatPlayer->getPlayer(), LoadBalancer::TEMPLATE_TYPE_HUNGER_GAME);
			})
		);

		$l_Window->addPart((new Button())
			->setText((new TextFormatter("template.sw"))->asStringForPlayer($p_Player))
			->setCallback(function () use ($l_FatPlayer)
			{
				LoadBalancer::getInstance()->balancePlayer($l_FatPlayer->getPlayer(), LoadBalancer::TEMPLATE_TYPE_SKYWAR);
			})
		);

		$l_Window->addPart((new Button())
			->setText((new TextFormatter("template.md"))->asStringForPlayer($p_Player))
			->setCallback(function () use ($l_FatPlayer)
			{
				LoadBalancer::getInstance()->balancePlayer($l_FatPlayer->getPlayer(), LoadBalancer::TEMPLATE_TYPE_MURDER);
			})
		);

		$l_Window->addPart((new Button())
			->setText((new TextFormatter("template.pk"))->asStringForPlayer($p_Player))
			->setCallback(function () use ($l_FatPlayer)
			{
				LoadBalancer::getInstance()->balancePlayer($l_FatPlayer->getPlayer(), LoadBalancer::TEMPLATE_TYPE_PARKOUR);
			})
		);

		$l_Window->addPart((new Button())
			->setText((new TextFormatter("template.random"))->asStringForPlayer($p_Player))
			->setCallback(function () use ($l_FatPlayer)
			{
				$l_ChoosedServer = LoadBalancer::getInstance()->getRandomNonEmptyServer([
					LoadBalancer::TEMPLATE_TYPE_BEDWAR,
					LoadBalancer::TEMPLATE_TYPE_HUNGER_GAME,
					LoadBalancer::TEMPLATE_TYPE_SKYWAR,
					LoadBalancer::TEMPLATE_TYPE_MURDER
				]);

				if (!is_null($l_ChoosedServer))
				{
					$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("template.sendTo", ["name" => new TextFormatter("template." . $l_ChoosedServer["type"])]))->asStringForFatPlayer($l_FatPlayer));
					LoadBalancer::getInstance()->transferPlayer($l_FatPlayer->getPlayer(), $l_ChoosedServer["ip"], $l_ChoosedServer["port"], "");
				} else
					$l_FatPlayer->getPlayer()->sendMessage((new TextFormatter("template.noAvailable"))->asStringForFatPlayer($l_FatPlayer));
			})
		);


		$l_Window->open();
    }
}

