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
        "Bat" => ["id" => 19, "height" => 0.8, "width" => 0.5, "fly" => true, "offsetY" => 1.2],
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
        "Parrot" => ["id" => 30, "height" => 0.8, "width" => 0.5, "fly" => true, "offsetY" => 1.2],

        "Zombie" => ["id" => 32, "height" => 1.95, "width" => 0.6, "speed" => 0.1],
        "Creeper" => ["id" => 33, "height" => 1.7, "width" => 0.6],
        "Skeleton" => ["id" => 34, "height" => 1.99, "width" => 0.6],
        "Spider" => ["id" => 35, "height" => 1.4, "width" => 0.9],
        "ZombiePigman" => ["id" => 36, "height" => 1.95, "width" => 0.6],
        "Slime" => ["id" => 37, "height" => 0.51, "width" => 0.51, "jump" => true],
        "Enderman" => ["id" => 38, "height" => 2.9, "width" => 0.6],
        "Silverfish" => ["id" => 39, "height" => 0.3, "width" => 0.4],
        "CaveSpider" => ["id" => 40, "height" => 0.5, "width" => 0.7],
        "Ghast" => ["id" => 41, "height" => 4, "width" => 4, "fly" => true, "distOffset" => 0.5, "offsetY" => 1.2, "scale" => 0.1],
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
        "Smoker" => ["id" => 102, "height" => 1, "width" => 1, "fly" => true],

        "Evoker" => ["id" => 104, "height" => 1.95, "width" => 0.6],
        "Vex" => ["id" => 105, "height" => 0.8, "width" => 0.4, "fly" => true, "offsetY" => 1.2],

        //LôL
        "BigSilverfish" => ["id" => 39, "height" => 0.3, "width" => 0.4, "scale" => 50, "distOffset" => 25],
        "BigVex" => ["id" => 105, "height" => 0.8, "width" => 0.4, "fly" => true, "offsetY" => -20, "scale" => 50, "distOffset" => 25],

        //test color
        "cat" => ["id" => 22, "height" => 0.7, "width" => 0.6, "fly" => true, "speed" => 0, "color" => 2], //0->3
        "cat1" => ["id" => 22, "height" => 0.7, "width" => 0.6, "fly" => true, "speed" => 0], //0->3
        "c1" => ["id" => 22, "height" => 0.7, "width" => 0.6, "fly" => true, "speed" => 0,  "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/3"]], //0->3
        "truc" => ["id" => 30, "height" => 0.8, "width" => 0.5, "fly" => true, "offsetY" => 1.2, "speed" => 0],

        //test horse
//        "h1" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/0"]], // white
//        "h2" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/1"]], // light light brown ^^
//        "h3" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/2"]], // light brown
//        "h4" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/3"]], // brown
//        "h5" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/4"]], // black
//        "h6" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/5"]], // grey
//        "h7" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/6"]], // dark brown

//        "h1" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/2", "DATA_COLOR"=>"DATA_TYPE_BYTE/0"]],
//        "h2" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/20", "DATA_COLOR"=>"DATA_TYPE_BYTE/1"]],
//        "h3" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/21", "DATA_COLOR"=>"DATA_TYPE_BYTE/2"]],
//        "h4" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/27", "DATA_COLOR"=>"DATA_TYPE_BYTE/3"]],
//        "h5" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/35", "DATA_COLOR"=>"DATA_TYPE_BYTE/4"]],
//        "h6" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/46", "DATA_COLOR"=>"DATA_TYPE_BYTE/5"]],

        "h0" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/256"]],
        "h1" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/257"]],
        "h2" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/258"]],
        "h3" => ["id" => 23, "height" => 1.6, "width" => 1.4, "options" => ["DATA_VARIANT"=>"DATA_TYPE_INT/259"]],
    ];
}