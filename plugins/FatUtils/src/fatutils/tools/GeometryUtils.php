<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 10:23
 */

namespace fatutils\tools;


use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class GeometryUtils
{
	public static function relativeToPosition(Position $p_Location, float $p_Pitch, float $p_Yaw, float $p_Distance)
	{
		$base = new Vector3(0, 0, 1);

        $v1 = $base->asVector3();
        $v1->x = 0;
        $v1->y = $base->y * cos(deg2rad($p_Pitch)) - $base->z * sin(deg2rad($p_Pitch));
        $v1->z = $base->z * cos(deg2rad($p_Pitch)) + $base->y * sin(deg2rad($p_Pitch));
        $v2 = $v1->asVector3();
        $v2->x = $v1->x * cos(deg2rad($p_Yaw)) - $v1->z * sin(deg2rad($p_Yaw));
        $v2->z = $v1->x * sin(deg2rad($p_Yaw)) + $v1->z * cos(deg2rad($p_Yaw));

//        $base1 = new Vector3();
//        $base1->z = $v2->y * sin(deg2rad(0)) + $v2->z * cos(deg2rad(0));
//        $base1->y = $v2->y * cos(deg2rad(0)) - $v2->z * sin(deg2rad(0));
//        $base1->x = $v2->x;

        $v2->multiply($p_Distance);
        return Position::fromObject($p_Location)->add($v2->getX(), $v2->getY(), $v2->getZ());
	}
}