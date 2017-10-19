<?php
namespace fatutils\pets;

use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;

/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 17/10/2017
 * Time: 14:13
 */

class PetTypes
{
    const SQUID = "Squid";
    const VILLAGER = "Villager";
    const ZOMBIE = "Zombie";
    const PIG = "Pig";

    const ENTITIES = [
        "Squid" => 17,
        "Villager" => 15,
        "Zombie" => 32,
        "Pig" => 12
    ];
}