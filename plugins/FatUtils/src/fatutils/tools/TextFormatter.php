<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 02/10/2017
 * Time: 16:15
 */

namespace fatutils\tools;


use fatutils\FatUtils;
use fatutils\players\FatPlayer;
use fatutils\players\PlayersManager;
use pocketmine\Player;
use pocketmine\utils\Config;

/**
 * Class TextFormatter
 * @package fatutils\tools
 *
 * Language lines are stored inside FatUtils resources (ex: FatUtils.phar/resources/langEN.properties)
 *
 * USAGE examples:
 *
 *  - simple :
        echo (new TextFormatter("money.gold.name"))->asStringForPlayer($l_Player);
 *
 *  - nested
        echo (new TextFormatter("player.earn"))
            ->addParam("name", "MACHIN")
            ->addParam("quantity", 5)
            ->addParam("moneyName", new TextFormatter("money.gold.name", [
                "version" => "v0.1"
            ]))
            ->asString() . "\n";
 */

class TextFormatter
{
    const LANG_ID_EN = 0;
    const LANG_ID_FR = 1;
    const LANG_ID_ES = 2;
    const LANG_ID_DEFAULT = TextFormatter::LANG_ID_FR;

    public static $m_AvailableLanguages = [
        TextFormatter::LANG_ID_EN => "EN",
        TextFormatter::LANG_ID_FR => "FR",
        TextFormatter::LANG_ID_ES => "ES"
    ];

    private static $m_LangsLines = null;

    private $m_Key = null;
    private $m_Params = [];

    public function __construct(string $p_Key, array $p_Params = null)
    {
        $this->m_Key = $p_Key;

        if (!is_null($p_Params))
            $this->m_Params = $p_Params;
    }

    public static function loadLanguages()
    {
        self::$m_LangsLines = [];
        FatUtils::getInstance()->getLogger()->info("TextFormatter Loading...");
        foreach (self::$m_AvailableLanguages as $l_Index => $l_LangName)
        {
            $l_Config = new Config(FatUtils::getInstance()->getDataFolder() . "lang" . $l_LangName . ".properties", Config::PROPERTIES);
            self::$m_LangsLines[$l_Index] = $l_Config;
            FatUtils::getInstance()->getLogger()->info("   - Loaded" . $l_LangName . " with " . count($l_Config->getAll()) . " entries");
        }
    }

    public static function getFormattedText(string $p_Key, array $p_Params = [], int $p_LangId = TextFormatter::LANG_ID_DEFAULT):string
    {
        if (is_null(self::$m_LangsLines))
            self::loadLanguages();

        $l_Ret = $p_Key;
        if (isset(self::$m_LangsLines[$p_LangId]))
        {
            $l_LangLines = self::$m_LangsLines[$p_LangId];
            if ($l_LangLines instanceof Config && $l_LangLines->exists($p_Key))
            {
                $l_Ret = $l_LangLines->get($p_Key);
                $l_Ret = str_replace("\\n", "\n", $l_Ret); // cause Config escape backslash when reading them

                foreach ($p_Params as $l_Index => $l_Param)
                {
                    if ($l_Param instanceof TextFormatter)
                        $l_Ret = str_replace("{".$l_Index."}", $l_Param->asString($p_LangId), $l_Ret);
                    else
                        $l_Ret = str_replace("{".$l_Index."}", $l_Param, $l_Ret);
                }
            }
            else
            {
                FatUtils::getInstance()->getLogger()->warning("Lang" . self::$m_AvailableLanguages[$p_LangId] . " \"" . $p_Key . "\" key is missing");
                //TODO add key to config ?
            }
        }

        return $l_Ret;
    }

    public function addParam(string $p_Key, $p_Param):TextFormatter
    {
        $this->m_Params[$p_Key] = $p_Param;
        return $this;
    }

    public function asStringForPlayer(Player $p_Player = null):string
    {
        if (!is_null($p_Player))
            return $this->asString(PlayersManager::getInstance()->getFatPlayer($p_Player));
        else
            return $this->asString();
    }

    public function asStringForFatPlayer(FatPlayer $p_Player = null):string
    {
        if (!is_null($p_Player))
            return $this->asString($p_Player->getLanguage());
        else
            return $this->asString();
    }

    public function asString(int $p_LanguageId = TextFormatter::LANG_ID_DEFAULT):string
    {
        return self::getFormattedText($this->m_Key, $this->m_Params, $p_LanguageId);
    }

    public function toString():string
    {
        return $this->m_Key . " " . $this->m_Params;
    }
}