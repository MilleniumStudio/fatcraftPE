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
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;

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
		return $p_Block->getLevel()->getBlock(new Position($p_Block->getX() + $x, $p_Block->getY() + $y, $p_Block->getZ() + $z));
	}

	public static function setBlocksId(array $p_Blocks, int $p_Id)
	{
		foreach ($p_Blocks as $l_Block)
		{
			if ($l_Block instanceof Block)
				$l_Block->getLevel()->setBlock($l_Block, BlockFactory::get($p_Id), true, true);
		}
	}
}