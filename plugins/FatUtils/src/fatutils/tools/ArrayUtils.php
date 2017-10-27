<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 12/10/2017
 * Time: 11:22
 */

namespace fatutils\tools;


class ArrayUtils
{
	public static function arrayToLowerCase(array $p_Array):array
	{
		foreach ($p_Array as $key => $l_Elem)
		{
			if (gettype($l_Elem) === 'string')
				$p_Array[$key] = strtolower($l_Elem);
		}
		return $p_Array;
	}

	public static function parseCmd(array $p_Options, array $p_Args):array
	{
		$p_Options = self::arrayToLowerCase($p_Options);
		$l_LowerCasedArgs = self::arrayToLowerCase($p_Args);

		$l_Ret = [];

		foreach ($p_Options as $l_Option)
		{
			$l_OptionIndex = array_search($l_Option, $l_LowerCasedArgs);
			if ($l_OptionIndex)
			{
				$l_Ret[$l_Option] = [];
				for ($i = $l_OptionIndex, $l = count($p_Args); $i < $l; $i++)
				{
					if ($l_LowerCasedArgs[$i] != $l_Option)
					{
						if (!in_array($l_LowerCasedArgs[$i], $p_Options))
							$l_Ret[$l_Option][] = $p_Args[$i];
						else
							break;
					}
				}
			}
		}

		return $l_Ret;
	}

	public static function getKeyOrDefault(array &$p_Array, string $p_Key, $p_Default = null)
	{
		if (array_key_exists($p_Key, $p_Array))
			return $p_Array[$p_Key];
		else
			return $p_Default;
	}
}