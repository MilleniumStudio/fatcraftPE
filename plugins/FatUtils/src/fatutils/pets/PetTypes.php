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
        "Chicken" => ["id" => 10, "height" => 0.7, "width" => 0.4, "fly" => true],
        "Cow" => ["id" => 11, "height" => 1.4, "width" => 0.9],
        "Pig" => ["id" => 12, "height" => 0.9, "width" => 0.9],
        "Sheep" => ["id" => 13, "height" => 1.3, "width" => 0.9],
        "Wolf" => ["id" => 14, "height" => 0.85, "width" => 0.6],
        "Villager" => ["id" => 15, "height" => 1.95, "width" => 0.6],
        "Mooshroom" => ["id" => 16, "height" => 1.4, "width" => 0.9],
        "Squid" => ["id" => 17, "height" => 0.8, "width" => 0.8, "jump" => true],
        "Bunny" => ["id" => 18, "height" => 0.5, "width" => 0.4, "jump" => true],
        "Bat" => ["id" => 19, "height" => 0.9, "width" => 0.5, "fly" => true],
        "IronGolem" => ["id" => 20, "height" => 2.7, "width" => 1.4],
        "Snowman" => ["id" => 21, "height" => 1.9, "width" => 0.7],
        "Ocelot" => ["id" => 22, "height" => 0.7, "width" => 0.6],
        "Horse" => ["id" => 23, "height" => 1.6, "width" => 1.4],
        "Donkey" => ["id" => 24, "height" => 1.6, "width" => 1.4],
        "Mule" => ["id" => 25, "height" => 1.6, "width" => 1.4],
        "SkeletonHorse" => ["id" => 26, "height" => 1.6, "width" => 1.4],
        "ZombieHorse" => ["id" => 27, "height" => 1.6, "width" => 1.4],
        "PolarBear" => ["id" => 28, "height" => 1.4, "width" => 1.3],
        "Llama" => ["id" => 29, "height" => 1.87, "width" => 0.9],
        "Parrot" => ["id" => 30, "height" => 1, "width" => 1, "fly" => true],

        "Zombie" => ["id" => 32, "height" => 1.95, "width" => 0.6, "speed" => 0.1],
        "Creeper" => ["id" => 33, "height" => 1.7, "width" => 0.6],
        "Skeleton" => ["id" => 34, "height" => 1.99, "width" => 0.6],
        "Spider" => ["id" => 35, "height" => 1.4, "width" => 0.9],
        "ZombiePigman" => ["id" => 36, "height" => 1.95, "width" => 0.6],
        "Slime" => ["id" => 37, "height" => 0.51, "width" => 0.51, "jump" => true],
        "Enderman" => ["id" => 38, "height" => 2.9, "width" => 0.6],
        "Silverfish" => ["id" => 39, "height" => 0.3, "width" => 0.4],
        "CaveSpider" => ["id" => 40, "height" => 0.5, "width" => 0.7],
        "Ghast" => ["id" => 41, "height" => 4, "width" => 4, "fly" => true, "distOffset" => 4],
        "MagmaCube" => ["id" => 42, "height" => 0.51, "width" => 0.51, "jump" => true],
        "Blaze" => ["id" => 43, "height" => 1.8, "width" => 0.6, "fly" => true],
        "ZombieVillager" => ["id" => 44, "height" => 1.95, "width" => 0.6],
        "Witch" => ["id" => 45, "height" => 1.95, "width" => 0.6],
        "Stray" => ["id" => 46, "height" => 1.99, "width" => 0.6],
        "Husk" => ["id" => 47, "height" => 1.95, "width" => 0.6],
        "WitherSkeleton" => ["id" => 48, "height" => 2.4, "width" => 0.7],
        "Guardian" => ["id" => 49, "height" => 0.85, "width" => 0.85, "jump" => true],
        "Guardian2" => ["id" => 50, "height" => 1, "width" => 1, "jump" => true],

        "Wither" => ["id" => 52, "height" => 3.5, "width" => 0.9],
        "EnderDragon" => ["id" => 53, "height" => 8, "width" => 16],
        "Endermite" => ["id" => 55, "height" => 0.3, "width" => 0.4],
        "Vindicator" => ["id" => 57, "height" => 1.95, "width" => 0.6],

        "Potion" => ["id" => 101, "height" => 0.4, "width" => 0.4],
        "Smoker" => ["id" => 102, "height" => 1, "width" => 1],

        "Evoker" => ["id" => 104, "height" => 1.95, "width" => 0.6],
        "Vex" => ["id" => 105, "height" => 0.8, "width" => 0.4, "fly" => true],
    ];

//    const ENTITIES = [
//        "Bat" => [19, 0.5, 0.9],
//        "Blaze" => [43, 0.6, 1.8],
//        "CaveSpider" => [40, 0.7, 0.5],
//        "Chicken" => [10, 0.4, 0.7],
//        "Cow" => [11, 0.9, 1.4],
//        "Creeper" => [33, 0.6, 1.7],
//        "Enderman" => [38, 0.6, 2.9],
//        "Ghast" => [41, 4, 4],
//        "IronGolem" => [20, 1.4, 2.7],
//        "MagmaCube" => [42, 0.51, 0.51],
//        "Mooshroom" => [16, 0.9, 1.4],
//        "Ocelot" => [22, 0.6, 0.7],
//        "Pig" => [12, 0.9, 0.9],
//        "ZombiePigman" => [36, 0.6, 1.95],
//        "Sheep" => [13, 0.9, 1.3],
//        "Skeleton" => [34, 0.6, 1.99],
//        "Slime" => [37, 0.51, 0.51],
//        "Snowman" => [21, 0.7, 1.9],
//        "Spider" => [35, 0.9, 1.4],
//        "Squid" => [17, 0.8, 0.8],
//        "Villager" => [15, 0.6, 1.95],
//        "Wolf" => [14, 0.6, 0.85],
//        "Zombie" => [32, 0.6, 1.95],
//        "ZombieVillager" => [44, 0.6, 1.95],
//        "Husk" => [47, 0.6, 1.95],
//
//        "Parrot" => [-1, 0.9, 0.5],
//        "Llama" => [29, 0.9, 1.87],
//        "Horse" => [23, 1.4, 1.6],
//        "Donkey" => [24, 1.4, 1.6],
//        "Mule" => [25, 1.4, 1.6],
//        "ZombieHorse" => [27, 1.4, 1.6],
//        "SkeletonHorse" => [26, 1.4, 1.6],
//        "Guardian" => [49, 0.85, 0.85],
//        "Silverfish" => [39, 0.4, 0.3],
//        "Vex" => [105, 0.4, 0.8],
//        "Bunny" => [18, 0.4, 0.5],
//        "PolarBear" => [28, 1.3, 1.4],
//        "Witch" => [45, 0.6, 1.95],
//        "Stray" => [46, 0.6, 1.99],
//        "Vindicator" => [57, 0.6, 1.95],
//        "WitherSkeleton" => [48, 0.7, 2.4],
//        "Wither" => [52, 0.9, 3.5],
//        "EnderDragon" => [53, 16, 8],
//        "Endermite" => [55, 0.4, 0.3],
//        "Evoker" => [104, 0.6, 1.95],
//
//
//        "t1" => [30, 1, 1],
//        "t2" => [31, 1, 1],
//        "t3" => [50, 1, 1],
//        "t4" => [51, 1, 1],
//        "t5" => [9, 1, 1],
//        "t6" => [58, 1, 1],
//        "t7" => [101, 1, 1],
//        "t8" => [102, 1, 1],
//        "t9" => [103, 1, 1],
//        "t10" => [106, 1, 1],
//
//        "a1" => [0, 1, 1],
//        "a2" => [1, 1, 1],
//        "a3" => [2, 1, 1],
//        "a4" => [3, 1, 1],
//        "a5" => [4, 1, 1],
//        "a6" => [5, 1, 1],
//        "a7" => [6, 1, 1],
//        "a8" => [7, 1, 1],
//        "a9" => [8, 1, 1],
//
//    ];
}