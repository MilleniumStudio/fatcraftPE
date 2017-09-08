<?php
namespace hungergames\lib\utils;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
class exc{
    const CHARS = [
        "=", "&", "<", ">", "/", "$", "#", "!", "-", "_", "+", ".", "@",
        "(", ")", "*", "^", "%", ";", ":", "?", "[", "]", "{", "}", "~"
    ];
    /**
     * @param $string
     * @param array|null $elements
     * @return mixed
     */
    public static function _($string, array $elements = null){
        if(count($elements) == 1){
            $string = str_replace("$0", $elements[0], $string);
            $string = str_replace("%%", "\xc2\xa7", $string);
            $string = str_replace("%n", "\n", $string);
            return $string;
        }
        for($i = 0; $i < count($elements); ++$i){
            $string = str_replace("$$i", $elements[$i], $string);
        }
        $string = str_replace("%%", "\xc2\xa7", $string);
        $string = str_replace("%n", "\n", $string);
        return $string;
    }
    /**
     * @param $val
     * @param string $exploder
     * @return string
     */
    public static function double($val, $exploder = "."){
        return number_format((float)$val, 2, $exploder, "");
    }
    /**
     * @param $v
     * @return bool|int
     */
    public static function checkIsNumber($v){
        if(is_numeric($v))
            return true;
        elseif(is_int($v))
            return true;
        elseif(is_float($v))
            return true;
        return false;
    }
    /**
     * @param $val
     * @return int
     */
    public static function stringToInteger($val){
        return self::checkIsNumber($val) ? intval($val) : 0;
    }
    /**
     * @param array $val
     * @return null
     */
    public static function randomValue(array $val){
        if(empty($val)) return null;
        return $val[array_rand($val)];
    }
    /**
     * @param $length
     * @return int
     */
    public static function randomNumber($length){
        $num = range(0, 9);
        $n = "";
        for ($i = 0; $i < $length; ++$i) {
            $n .= exc::randomValue($num);
        }
        return intval($n);
    }
    /**
     * @param $length
     * @param bool|true $numbers
     * @param bool|false $chars
     * @return string
     */
    public static function randomString($length, $numbers = true, $chars = false){
        $abc = range("A", "Z");
        $num = range(0, 9);
        $str = "";
        if(!$numbers and !$chars){
            for ($i = 0; $i < $length; ++$i) {
                $str .= exc::randomValue($abc);
            }
        }
        if($numbers) {
            for ($i = 0; $i < $length / 2; ++$i) {
                $str .= exc::randomValue($abc);
                $str .= exc::randomValue($num);
            }
        }
        if($chars){
            for($i = 0; $i < $length / 2; ++$i){
                $str .= exc::randomValue($abc);
                $str .= exc::randomValue(exc::CHARS);
            }
        }
        return $str;
    }
    /**
     * @param $string
     * @param bool|false $numbers
     * @param bool|false $chars
     * @return string
     */
    public static function mixString($string, $numbers = false, $chars = false){
        $num = range(0, 9);
        $str = "";
        if(!$numbers and !$chars){
            for($i = 0; $i < strlen($string); ++$i){
                $str .= $string[$i];
            }
        }
        if($numbers) {
            for ($i = 0; $i < strlen($string); ++$i) {
                $str .= $string[$i];
                $str .= exc::randomValue($num);
            }
        }
        if($chars){
            for($i = 0; $i < strlen($string); ++$i){
                $str .= $string[$i];
                $str .= exc::randomValue(exc::CHARS);
            }
        }
        return $str;
    }
    /**
     * @param $string
     * @return array
     */
    public static function getChars($string){
        preg_match_all("/[[:punct:]]/", $string, $m);
        return $m[0];
    }
    /**
     * @param $string
     * @return string
     */
    public static function replaceChars($string){
        foreach(exc::getChars($string) as $char){
            $string = str_replace($char, "", $string);
        }
        return $string;
    }
    /**
     * @param $string
     * @return string
     */
    public static function replaceAllKeepLetters($string){
        return preg_replace("![^a-z0-9]+!i", "", $string);
    }
    /**
     * @param $string
     * @return mixed
     */
    public static function clearColors($string){
        $colors = ["&a", "&b", "&c", "&d", "&e", "&f", "&r", "&k", "&l", "&o"];
        for($i = 0; $i < 10; ++$i){
            $string = str_replace("&$i", "", $string);
        }
        foreach($colors as $c){
            $string = str_replace($c, "", $string);
        }
        return $string;
    }
    /**
     * @param array $values
     * @return array
     */
    public static function returnArrayOfMultiArray(array $values){
        $result = [];
        $values = new RecursiveIteratorIterator(new RecursiveArrayIterator($values));
        foreach($values as $key => $v) {
            $result[$key] = $v;
        }
        return $result;
    }
    /**
     * @param $filePath
     * @return array
     */
    public static function getFileClasses($filePath) {
        $php_code = file_get_contents($filePath);
        $classes = self::getPHPClasses($php_code);
        return $classes;
    }
    /**
     * @param $php_code
     * @return array
     */
    public static function getPHPClasses($php_code) {
        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i-2][0] == T_CLASS
                and $tokens[$i-1][0] == T_WHITESPACE
                and $tokens[$i][0] == T_STRING) {
                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        return $classes;
    }
    /**
     * @param $key
     * @param $haystack
     * @return bool
     */
    public static function array_key_exists_md($key, $haystack){
        $haystack = self::returnArrayOfMultiArray($haystack);
        foreach($haystack as $r => $v){
            if($key === $r or $key === $v) return true;
        }
        return false;
    }
}