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
}