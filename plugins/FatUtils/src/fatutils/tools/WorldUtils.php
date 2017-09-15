<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 13:53
 */

namespace fatutils\tools;


use fatutils\FatUtils;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\network\mcpe\protocol\AddEntityPacket;

class WorldUtils
{
	public static function stringToLocation(string $p_RawLoc):Location
	{
		$x = 0.0;
		$y = 0.0;
		$z = 0.0;
		$yaw = 0.0;
		$pitch = 0.0;
		$level = FatUtils::getInstance()->getServer()->getLevel(1);

		$array = explode("/", $p_RawLoc);
		if (count($array) === 3)
		{
			$x = doubleval($array[0]);
			$y = doubleval($array[1]);
			$z = doubleval($array[2]);
		}
		else if (count($array) === 4)
		{
			$level = FatUtils::getInstance()->getServer()->getLevelByName($array[0]);
			$x = doubleval($array[1]);
			$y = doubleval($array[2]);
			$z = doubleval($array[3]);
		} else if (count($array) === 5)
		{
			$x = doubleval($array[0]);
			$y = doubleval($array[1]);
			$z = doubleval($array[2]);
			$yaw = doubleval($array[3]);
			$pitch = doubleval($array[4]);
		} else if (count($array) === 6)
		{
			$level = FatUtils::getInstance()->getServer()->getLevelByName($array[0]);
			$x = doubleval($array[1]);
			$y = doubleval($array[2]);
			$z = doubleval($array[3]);
			$yaw = doubleval($array[4]);
			$pitch = doubleval($array[5]);
		}

		return new Location($x, $y, $z, $yaw, $pitch, $level);
	}

	public static function locationToString(Location $p_Loc):string
	{
		return (is_null($p_Loc->getLevel()) ? "" : $p_Loc->getLevel()->getName() . "/") . $p_Loc->getX() . "/" . $p_Loc->getY() . "/" . $p_Loc->getZ() . "/" . $p_Loc->getYaw() . "/" . $p_Loc->getPitch();
	}

	public static function getRadiusBB(Position $p_Pos, float $p_Radius):AxisAlignedBB {
		return new AxisAlignedBB(
			$p_Pos->getX() - $p_Radius,
			$p_Pos->getY() - $p_Radius,
			$p_Pos->getZ() - $p_Radius,
			$p_Pos->getX() + $p_Radius,
			$p_Pos->getY() + $p_Radius,
			$p_Pos->getZ() + $p_Radius);
	}

	public static function getRelativeBlock(Block $p_Block, int $x, int $y, int $z):Block
	{
            $_Pos = new Position($p_Block->getX() + $x, $p_Block->getY() + $y, $p_Block->getZ() + $z);
            self::loadChunkAt($_Pos);
            return $p_Block->getLevel()->getBlock($_Pos);
	}

	public static function getRandomizedLocation(Location $p_StartLocation, float $p_XBound, float $p_YBound, float $p_ZBound)
    {
        $l_NewX = MathUtils::rand(-$p_XBound, $p_XBound);
        $l_NewY = MathUtils::rand(-$p_YBound, $p_YBound);
        $l_NewZ = MathUtils::rand(-$p_ZBound, $p_ZBound);

        return new Location($p_StartLocation->getX() + $l_NewX, $p_StartLocation->getY() + $l_NewY, $p_StartLocation->getZ() + $l_NewZ, $p_StartLocation->getYaw(), $p_StartLocation->getPitch(), $p_StartLocation->getLevel());
    }

	public static function setBlocksId(array $p_Blocks, int $p_Id)
	{
		foreach ($p_Blocks as $l_Block)
		{
			if ($l_Block instanceof Block)
				$l_Block->getLevel()->setBlock($l_Block, BlockFactory::get($p_Id), true, true);
		}
	}

	public static function loadChunkAt(Position $p_Pos)
    {
        $p_Pos->getLevel()->loadChunk($p_Pos->getFloorX() >> 4, $p_Pos->getFloorZ() >> 4);
    }

    public static function addStrike(Location $p_Loc, $height = 0){

        $level = $p_Loc->getLevel();

        $light = new AddEntityPacket();

        $light->type = 93;
        $light->entityUniqueId = Entity::$entityCount++;
        $light->entityRuntimeId = $light->entityUniqueId;
        $light->metadata = array();
        $light->speedX = 0;
        $light->speedY = 0;
        $light->speedZ = 0;
        $light->yaw = $p_Loc->getYaw();
        $light->pitch = $p_Loc->getPitch();
        $light->x = $p_Loc->x;
        $light->y = $p_Loc->y + $height;
        $light->z = $p_Loc->z;

//        $level->addSound(new GenericSound($p_Loc, LevelEventPacket::EVENT_SOUND_CLICK, 1));
        FatUtils::getInstance()->getServer()->broadcastPacket($level->getPlayers(), $light);
    }

    public static function stopWorldsTime()
    {
        foreach (FatUtils::getInstance()->getServer()->getLevels() as $l_Level)
            $l_Level->stopTime();
    }
}