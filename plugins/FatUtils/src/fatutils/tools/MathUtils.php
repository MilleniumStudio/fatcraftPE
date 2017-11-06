<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 12/09/2017
 * Time: 16:44
 */

namespace fatutils\tools;


class MathUtils
{
    public static function rand($min = 0, $max = 1)
    {
        return ($min + ($max - $min) * (mt_rand() / mt_getrandmax()));
    }

	public static function frand($min, $max, $decimals = 0) {
		$scale = pow(10, $decimals);
		return mt_rand($min * $scale, $max * $scale) / $scale;
	}
}