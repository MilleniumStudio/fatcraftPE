<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 05/10/2017
 * Time: 11:26
 */

namespace fatutils\tools;


use pocketmine\utils\Color;
use pocketmine\utils\TextFormat;

class ColorUtils
{
    const BLACK = "BLACK";
    const DARK_BLUE = "DARK_BLUE";
    const DARK_GREEN = "DARK_GREEN";
    const DARK_AQUA = "DARK_AQUA";
    const DARK_RED = "DARK_RED";
    const DARK_PURPLE = "DARK_PURPLE";

    const GOLD = "GOLD";
    const GRAY = "GRAY";
    const DARK_GRAY = "DARK_GRAY";
    const BLUE = "BLUE";
    const GREEN = "GREEN";
    const AQUA = "AQUA";
    const RED = "RED";
    const LIGHT_PURPLE = "LIGHT_PURPLE";
    const YELLOW = "YELLOW";
    const WHITE = "WHITE";

    public static function getMetaFromColor(string $p_Color):int
    {
        switch ($p_Color)
        {
            case ColorUtils::BLACK:
                return 15;
            case ColorUtils::DARK_BLUE:
                return 12;
            case ColorUtils::DARK_GREEN:
                return 13;
            case ColorUtils::DARK_AQUA:
                return 9;
            case ColorUtils::DARK_RED:
                return 14;
            case ColorUtils::DARK_PURPLE:
                return 10;
            case ColorUtils::GOLD:
                return 1;
            case ColorUtils::GRAY:
                return 8;
            case ColorUtils::DARK_GRAY:
                return 7;
            case ColorUtils::BLUE:
                return 11;
            case ColorUtils::GREEN:
                return 5;
            case ColorUtils::AQUA:
                return 3;
            case ColorUtils::RED:
                return 14; // same as DARK_RED, otherwise it's ugly...
            case ColorUtils::LIGHT_PURPLE:
                return 6;
            case ColorUtils::YELLOW:
                return 4;
            case ColorUtils::WHITE:
                return 0;
        }

        return 0;
    }

    public static function getTextFormatFromColor(string $p_Color):string
    {
        switch ($p_Color)
        {
            case ColorUtils::BLACK:
                return TextFormat::BLACK;
            case ColorUtils::DARK_BLUE:
                return TextFormat::DARK_BLUE;
            case ColorUtils::DARK_GREEN:
                return TextFormat::DARK_GREEN;
            case ColorUtils::DARK_AQUA:
                return TextFormat::DARK_AQUA;
            case ColorUtils::DARK_RED:
                return TextFormat::DARK_RED;
            case ColorUtils::DARK_PURPLE:
                return TextFormat::DARK_PURPLE;
            case ColorUtils::GOLD:
                return TextFormat::GOLD;
            case ColorUtils::GRAY:
                return TextFormat::GRAY;
            case ColorUtils::DARK_GRAY:
                return TextFormat::DARK_GRAY;
            case ColorUtils::BLUE:
                return TextFormat::BLUE;
            case ColorUtils::GREEN:
                return TextFormat::GREEN;
            case ColorUtils::AQUA:
                return TextFormat::AQUA;
            case ColorUtils::RED:
                return TextFormat::RED;
            case ColorUtils::LIGHT_PURPLE:
                return TextFormat::LIGHT_PURPLE;
            case ColorUtils::YELLOW:
                return TextFormat::YELLOW;
            case ColorUtils::WHITE:
                return TextFormat::WHITE;
        }

        return "";
    }

    public static function getColorFromColor(string $p_Color):Color
    {
        switch ($p_Color)
        {
            case ColorUtils::BLACK:
                return new Color(29,29,33);
            case ColorUtils::DARK_BLUE:
                return new Color(60,68,170);
            case ColorUtils::DARK_GREEN:
                return new Color(94,124,22);
            case ColorUtils::DARK_AQUA:
                return new Color(22,156,156);
            case ColorUtils::DARK_RED:
                return new Color(176,46,38);
            case ColorUtils::DARK_PURPLE:
                return new Color(137,50,184);
            case ColorUtils::GOLD:
                return new Color(249,128,29);
            case ColorUtils::GRAY:
                return new Color(157,157,151);
            case ColorUtils::DARK_GRAY:
                return new Color(71,79,82);
            case ColorUtils::BLUE:
                return new Color(0,102,255);
            case ColorUtils::GREEN:
                return new Color(128,199,31);
            case ColorUtils::AQUA:
                return new Color(58,179,218);
            case ColorUtils::RED:
                return new Color(255,46,38);
            case ColorUtils::LIGHT_PURPLE:
                return new Color(199,78,189);
            case ColorUtils::YELLOW:
                return new Color(254,216,61);
            case ColorUtils::WHITE:
                return new Color(249,255,254);
        }

        return new Color(249,255,254);
    }

    public static function getColorCode(Color $p_Color): int
    {
        return ($p_Color->getR() << 16 | $p_Color->getG() << 8 | $p_Color->getB() ) & 0xffffff;
    }
}