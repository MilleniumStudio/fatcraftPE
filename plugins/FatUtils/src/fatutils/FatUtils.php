<?php
namespace fatutils;

use pocketmine\plugin\PluginBase;

class FatUtils extends PluginBase
{
	private static $m_Instance;

	public static function getInstance():FatUtils
	{
		return self::$m_Instance;
	}

    public function onLoad()
	{
		self::$m_Instance = $this;
    }

    public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getCommandMap()->register("fatUtils", new FatUtilsCmd("fatUtils", "", null, ["fu"]));
    }
}
