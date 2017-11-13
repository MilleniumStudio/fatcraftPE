<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 09/11/2017
 * Time: 14:19
 */

namespace fatutils\powers\effects;


use fatutils\powers\APower;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\Server;

class Blindness extends APower
{

    function getIcon(): Item
    {
        return \pocketmine\item\ItemFactory::get(381);
    }

    function action(): bool
    {
        $this->destroy();
        $effect = Effect::getEffect(Effect::BLINDNESS);//new Effect(Effect::BLINDNESS, "text ?", 10, 10, 10, true, 5*20);
        $effect->setDuration(2 * 20);
        $effect->setAmplifier(0);
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($this->owner !== $player)
                $player->addEffect($effect);
        }
        return true;
    }
}