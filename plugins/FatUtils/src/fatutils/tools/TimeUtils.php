<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 07/11/2017
 * Time: 11:07
 */

namespace fatutils\tools;


class TimeUtils
{
	public static function getCurrentMillisec(): int
	{
		return round(microtime(true) * 1000);
	}
}