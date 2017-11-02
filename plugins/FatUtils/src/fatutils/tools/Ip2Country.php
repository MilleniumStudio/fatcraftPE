<?php

//Author: de77
//Homepage: http://de77.com
//Version: 01.06.2010
//Licence: MIT
//You can grab the database from http://software77.net/geo-ip/

class Ip2Country
{

    //fields
    public $dir = 'php_db/';
    public $codes = array(
        'AF' => 'AFGHANISTAN',
        'AL' => 'ALBANIA',
        'DZ' => 'ALGERIA',
        'AS' => 'AMERICAN SAMOA',
        'AD' => 'ANDORRA',
        'AO' => 'ANGOLA',
        'AI' => 'ANGUILLA',
        'AQ' => 'ANTARCTICA',
        'AG' => 'ANTIGUA AND BARBUDA',
        'AR' => 'ARGENTINA',
        'AM' => 'ARMENIA',
        'AW' => 'ARUBA',
        'AC' => 'ASCENSION ISLAND',
        'AU' => 'AUSTRALIA',
        'AT' => 'AUSTRIA',
        'AZ' => 'AZERBAIJAN',
        'BS' => 'BAHAMAS',
        'BH' => 'BAHRAIN',
        'BD' => 'BANGLADESH',
        'BB' => 'BARBADOS',
        'BY' => 'BELARUS',
        'BE' => 'BELGIUM',
        'BZ' => 'BELIZE',
        'BJ' => 'BENIN',
        'BM' => 'BERMUDA',
        'BT' => 'BHUTAN',
        'BO' => 'BOLIVIA',
        'BA' => 'BOSNIA AND HERZEGOWINA',
        'BW' => 'BOTSWANA',
        'BV' => 'BOUVET ISLAND',
        'BR' => 'BRAZIL',
        'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
        'BN' => 'BRUNEI DARUSSALAM',
        'BG' => 'BULGARIA',
        'BF' => 'BURKINA FASO',
        'BI' => 'BURUNDI',
        'KH' => 'CAMBODIA',
        'CM' => 'CAMEROON',
        'CA' => 'CANADA',
        'CV' => 'CAPE VERDE',
        'KY' => 'CAYMAN ISLANDS',
        'CF' => 'CENTRAL AFRICAN REPUBLIC',
        'TD' => 'CHAD',
        'CL' => 'CHILE',
        'CN' => 'CHINA',
        'CX' => 'CHRISTMAS ISLAND',
        'CC' => 'COCOS (KEELING) ISLANDS',
        'CO' => 'COLOMBIA',
        'KM' => 'COMOROS',
        'CD' => 'CONGO THE DEMOCRATIC REPUBLIC OF THE',
        'CG' => 'CONGO',
        'CK' => 'COOK ISLANDS',
        'CR' => 'COSTA RICA',
        'CI' => 'COTE D\'IVOIRE',
        'HR' => 'CROATIA',
        'CU' => 'CUBA',
        'CY' => 'CYPRUS',
        'CZ' => 'CZECH REPUBLIC',
        'DK' => 'DENMARK',
        'DJ' => 'DJIBOUTI',
        'DM' => 'DOMINICA',
        'DO' => 'DOMINICAN REPUBLIC',
        'TP' => 'EAST TIMOR',
        'EC' => 'ECUADOR',
        'EG' => 'EGYPT',
        'SV' => 'EL SALVADOR',
        'GQ' => 'EQUATORIAL GUINEA',
        'ER' => 'ERITREA',
        'EE' => 'ESTONIA',
        'ET' => 'ETHIOPIA',
        'EU' => 'EUROPEAN UNION',
        'FK' => 'FALKLAND ISLANDS (MALVINAS)',
        'FO' => 'FAROE ISLANDS',
        'FJ' => 'FIJI',
        'FI' => 'FINLAND',
        'FX' => 'FRANCE METRO',
        'FR' => 'FRANCE',
        'GF' => 'FRENCH GUIANA',
        'PF' => 'FRENCH POLYNESIA',
        'TF' => 'FRENCH SOUTHERN TERRITORIES',
        'GA' => 'GABON',
        'GM' => 'GAMBIA',
        'GE' => 'GEORGIA',
        'DE' => 'GERMANY',
        'GH' => 'GHANA',
        'GI' => 'GIBRALTAR',
        'GR' => 'GREECE',
        'GL' => 'GREENLAND',
        'GD' => 'GRENADA',
        'GP' => 'GUADELOUPE',
        'GU' => 'GUAM',
        'GT' => 'GUATEMALA',
        'GG' => 'GUERNSEY',
        'GN' => 'GUINEA',
        'GW' => 'GUINEA-BISSAU',
        'GY' => 'GUYANA',
        'HT' => 'HAITI',
        'HM' => 'HEARD AND MC DONALD ISLANDS',
        'VA' => 'HOLY SEE (VATICAN CITY STATE)',
        'HN' => 'HONDURAS',
        'HK' => 'HONG KONG',
        'HU' => 'HUNGARY',
        'IS' => 'ICELAND',
        'IN' => 'INDIA',
        'ID' => 'INDONESIA',
        'IR' => 'IRAN (ISLAMIC REPUBLIC OF)',
        'IQ' => 'IRAQ',
        'IE' => 'IRELAND',
        'IM' => 'ISLE OF MAN',
        'IL' => 'ISRAEL',
        'IT' => 'ITALY',
        'JM' => 'JAMAICA',
        'JP' => 'JAPAN',
        'JE' => 'JERSEY',
        'JO' => 'JORDAN',
        'KZ' => 'KAZAKHSTAN',
        'KE' => 'KENYA',
        'KI' => 'KIRIBATI',
        'KP' => 'KOREA DEMOCRATIC PEOPLE\'S REPUBLIC OF',
        'KR' => 'KOREA REPUBLIC OF',
        'KW' => 'KUWAIT',
        'KG' => 'KYRGYZSTAN',
        'LA' => 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC',
        'LV' => 'LATVIA',
        'LB' => 'LEBANON',
        'LS' => 'LESOTHO',
        'LR' => 'LIBERIA',
        'LY' => 'LIBYAN ARAB JAMAHIRIYA',
        'LI' => 'LIECHTENSTEIN',
        'LT' => 'LITHUANIA',
        'LU' => 'LUXEMBOURG',
        'MO' => 'MACAU',
        'MK' => 'MACEDONIA',
        'MG' => 'MADAGASCAR',
        'MW' => 'MALAWI',
        'MY' => 'MALAYSIA',
        'MV' => 'MALDIVES',
        'ML' => 'MALI',
        'MT' => 'MALTA',
        'MH' => 'MARSHALL ISLANDS',
        'MQ' => 'MARTINIQUE',
        'MR' => 'MAURITANIA',
        'MU' => 'MAURITIUS',
        'YT' => 'MAYOTTE',
        'MX' => 'MEXICO',
        'FM' => 'MICRONESIA FEDERATED STATES OF',
        'MD' => 'MOLDOVA REPUBLIC OF',
        'MC' => 'MONACO',
        'MN' => 'MONGOLIA',
        'MS' => 'MONTSERRAT',
        'MA' => 'MOROCCO',
        'MZ' => 'MOZAMBIQUE',
        'MM' => 'MYANMAR',
        'ME' => 'Montenegro',
        'NA' => 'NAMIBIA',
        'NR' => 'NAURU',
        'NP' => 'NEPAL',
        'AN' => 'NETHERLANDS ANTILLES',
        'NL' => 'NETHERLANDS',
        'NC' => 'NEW CALEDONIA',
        'NZ' => 'NEW ZEALAND',
        'NI' => 'NICARAGUA',
        'NE' => 'NIGER',
        'NG' => 'NIGERIA',
        'NU' => 'NIUE',
        'AP' => 'NON-SPEC ASIA PAS LOCATION',
        'NF' => 'NORFOLK ISLAND',
        'MP' => 'NORTHERN MARIANA ISLANDS',
        'NO' => 'NORWAY',
        'OM' => 'OMAN',
        'PK' => 'PAKISTAN',
        'PW' => 'PALAU',
        'PS' => 'PALESTINIAN TERRITORY OCCUPIED',
        'PA' => 'PANAMA',
        'PG' => 'PAPUA NEW GUINEA',
        'PY' => 'PARAGUAY',
        'PE' => 'PERU',
        'PH' => 'PHILIPPINES',
        'PN' => 'PITCAIRN',
        'PL' => 'POLAND',
        'PT' => 'PORTUGAL',
        'PR' => 'PUERTO RICO',
        'QA' => 'QATAR',
        'ZZ' => 'RESERVED',
        'RE' => 'REUNION',
        'RO' => 'ROMANIA',
        'RU' => 'RUSSIAN FEDERATION',
        'RW' => 'RWANDA',
        'KN' => 'SAINT KITTS AND NEVIS',
        'LC' => 'SAINT LUCIA',
        'VC' => 'SAINT VINCENT AND THE GRENADINES',
        'WS' => 'SAMOA',
        'SM' => 'SAN MARINO',
        'ST' => 'SAO TOME AND PRINCIPE',
        'SA' => 'SAUDI ARABIA',
        'SN' => 'SENEGAL',
        'SC' => 'SEYCHELLES',
        'SL' => 'SIERRA LEONE',
        'SG' => 'SINGAPORE',
        'SK' => 'SLOVAKIA (SLOVAK REPUBLIC)',
        'SI' => 'SLOVENIA',
        'SB' => 'SOLOMON ISLANDS',
        'SO' => 'SOMALIA',
        'ZA' => 'SOUTH AFRICA',
        'GS' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
        'ES' => 'SPAIN',
        'LK' => 'SRI LANKA',
        'SH' => 'ST. HELENA',
        'PM' => 'ST. PIERRE AND MIQUELON',
        'SD' => 'SUDAN',
        'SR' => 'SURINAME',
        'SJ' => 'SVALBARD AND JAN MAYEN ISLANDS',
        'SZ' => 'SWAZILAND',
        'SE' => 'SWEDEN',
        'CH' => 'SWITZERLAND',
        'SY' => 'SYRIAN ARAB REPUBLIC',
        'CS' => 'SERBIA AND MONTENEGRO',
        'YU' => 'SERBIA AND MONTENEGRO',
        'RS' => 'Serbia',
        'TW' => 'TAIWAN; REPUBLIC OF CHINA (ROC)',
        'TJ' => 'TAJIKISTAN',
        'TZ' => 'TANZANIA UNITED REPUBLIC OF',
        'TH' => 'THAILAND',
        'TL' => 'TIMOR-LESTE',
        'TG' => 'TOGO',
        'TK' => 'TOKELAU',
        'TO' => 'TONGA',
        'TT' => 'TRINIDAD AND TOBAGO',
        'TN' => 'TUNISIA',
        'TR' => 'TURKEY',
        'TM' => 'TURKMENISTAN',
        'TC' => 'TURKS AND CAICOS ISLANDS',
        'TV' => 'TUVALU',
        'UG' => 'UGANDA',
        'UA' => 'UKRAINE',
        'AE' => 'UNITED ARAB EMIRATES',
        'GB' => 'UNITED KINGDOM',
        'UK' => 'UNITED KINGDOM',
        'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS',
        'US' => 'UNITED STATES',
        'UY' => 'URUGUAY',
        'UZ' => 'UZBEKISTAN',
        'VU' => 'VANUATU',
        'VE' => 'VENEZUELA',
        'VN' => 'VIET NAM',
        'VG' => 'VIRGIN ISLANDS (BRITISH)',
        'VI' => 'VIRGIN ISLANDS (U.S.)',
        'WF' => 'WALLIS AND FUTUNA ISLANDS',
        'EH' => 'WESTERN SAHARA',
        'YE' => 'YEMEN',
        'ZM' => 'ZAMBIA',
        'ZW' => 'ZIMBABWE',
        'AX' => 'ALAND ISLANDS',
        'MF' => 'SAINT MARTIN'
    );
    //properties
    private $property = array(
        'country' => false,
        'countryCode' => false
    );

    //methods
    private function appendToFile($db)
    {
        foreach ($db AS $piece => $entries)
        {
            $filename = $this->dir . $piece . '.php';

            if (!file_exists($filename))
            {
                $f = fopen($filename, 'w');
                fputs($f, '<?php $entries = array(' . "\n");
            } else
            {
                $f = fopen($filename, 'a');
            }

            foreach ($entries AS $entry)
            {
                fputs($f, "array('" . $entry[0] . "','" . $entry[1] . "','" . $entry[2] . "'),\n");
            }

            fclose($f);
        }
    }

    private function finishFile($filename)
    {
        $f = fopen($filename, 'a');
        fputs($f, ');');
        fclose($f);
    }

    public function parseCSV($filename = 'IpToCountry.csv')
    {
        $f = fopen($filename, 'r');
        $db = array();

        //parse into array
        while (!feof($f))
        {
            $s = fgets($f);
            if (substr($s, 0, 1) == '#') continue;

            $temp = explode(',', $s);
            if (count($temp) < 7) continue;

            list($from, $to, $a, $b, $code, $c, $country) = $temp;

            $from = trim($from, '"');
            $to = trim($to, '"');
            $code = trim($code, '"');

            $piece = substr($from, 0, 3);

            $db[$piece][] = array($from, $to, $code);
        }
        fclose($f);

        //dump array into many PHP files
        foreach ($db AS $piece => $entries)
        {
            $f = fopen($this->dir . $piece . '.php', 'w');
            fputs($f, '<?php $entries = array(' . "\n");

            foreach ($entries AS $from => $entry)
            {
                fputs($f, "array('" . $entry[0] . "','" . $entry[1] . "','" . $entry[2] . "'),\n");
            }

            fputs($f, ');');
            fclose($f);
        }
    }

    public function parseCSV2($filename = 'IpToCountry.csv')
    {
        $f = fopen($filename, 'r');
        $db = array();
        $dbSize = 0;

        //parse into array
        while (!feof($f))
        {
            $s = fgets($f);

            if (substr($s, 0, 1) == '#') continue;

            $temp = explode(',', $s);
            if (count($temp) < 7) continue;

            list($from, $to, $a, $b, $code, $c, $country) = $temp;

            $from = trim($from, '"');
            $to = trim($to, '"');
            $code = trim($code, '"');

            $piece = substr($from, 0, 3);

            $db[$piece][] = array($from, $to, $code);
            $dbSize++;

            if ($dbSize > 100)
            {
                $this->appendToFile($db);
                unset($db);
                $dbSize = 0;
            }
        }
        fclose($f);

        $this->appendToFile($db);

        //now "finish" all files
        if (is_dir($this->dir))
        {
            if ($dh = opendir($this->dir))
            {
                while (($file = readdir($dh)) !== false)
                {
                    if ($file == '.' or $file == '..')
                    {
                        continue;
                    }
                    $this->finishFile($this->dir . $file);
                }
                closedir($dh);
            }
        }

        //echo memory_get_usage();		
    }

    public function load($ip)
    {
        $ip = floatval($this->ip2int($ip));
        $piece = substr($ip, 0, 3);

        if (!file_exists($this->dir . $piece . '.php'))
                {
            $this->property['countryCode'] = '?';
            $this->property['country'] = '?';

            return $this;
                }

        include $this->dir . $piece . '.php';

        foreach ($entries AS $e)
        {
            $e[0] = floatval($e[0]);

            if ($e[0] <= $ip and $e[1] >= $ip)
                        {
                $this->property['countryCode'] = $e[2];
                $this->property['country'] = $this->codes[$e[2]];
                return $this;
                        }
        }

        $this->property['countryCode'] = '?';
        $this->property['country'] = '?';

        return $this;
    }

    private function ip2int($ip)
    {
        //In case you wonder how it works...
        //$t = explode('.', $ip);
        //return $t[0] * 256*256*256 + $t[1]*256*256 + $t[2]*256 + $t[3];
        return sprintf("%u\n", ip2long($ip));
    }

    public function __get($var)
    {
        if (isset($this->property[$var]))
        {
            if ($this->property[$var] != false)
            {
                return $this->property[$var];
            }
            $this->error = 'No IP specified';
        }
        return false;
    }

}
