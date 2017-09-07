<?php
namespace fatutils;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class FatUtils extends PluginBase
{
    private $m_TemplateConfig;
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

    public function setTemplateConfig(Config $p_Config)
    {
        $this->m_TemplateConfig = $p_Config;
    }
}
