<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 12/09/2017
 * Time: 11:27
 */

namespace SalmonDE\StatsPE;


use pocketmine\Player;
use SalmonDE\StatsPE\Providers\Entry;
use SalmonDE\StatsPE\Providers\MySQLProvider;

/*
 *  Permits an easy way to add Stats.
 *
 * To add a new entry, add a line like
 *    $this->customEntries[] = new Entry('XP', 0, Entry::INT, true);
 * in the constructor. The system will add them automatically in database.
 * After this you can use the methods getEntry, modIntEntry and setEntry to modify the data from your plugin,
 * using the possibilities of singleton.
 * To test, you can try the ops' commands :
 *      /stats test <entryName> : for display the value of the specified stat
 *      /stats test <entryName> add <value> : to add a value to the specified stat (only for numeric stats)
 *      /stats test <entryName> <value> : to set the value of the specified stat
 */
class CustomEntries
{
    private static $instance;
    public $customEntries;
    public function __construct()
    {
        CustomEntries::$instance = $this;
        $this->customEntries[] = new Entry('Money', 0, Entry::INT, true);
        $this->customEntries[] = new Entry('XP', 0, Entry::INT, true);

        $this->customEntries[] = new Entry('pk_played'  , 0, Entry::INT, true);
        $this->customEntries[] = new Entry('pk_XP'      , 0, Entry::INT, true);
        $this->customEntries[] = new Entry('bw_played'  , 0, Entry::INT, true);
        $this->customEntries[] = new Entry('bw_XP'      , 0, Entry::INT, true);
        $this->customEntries[] = new Entry('hg_played'  , 0, Entry::INT, true);
        $this->customEntries[] = new Entry('hg_XP'      , 0, Entry::INT, true);
        $this->customEntries[] = new Entry('sw_played'  , 0, Entry::INT, true);
        $this->customEntries[] = new Entry('sw_XP'      , 0, Entry::INT, true);

        foreach ($this->customEntries as $customEntry) {
            Base::getInstance()->getDataProvider()->addEntry($customEntry);
        }
    }

    public static function getInstance() : CustomEntries
    {
        return CustomEntries::$instance;
    }

    public function getEntry(string $name, Player $player)
    {
        if(Base::getInstance()->getDataProvider()->entryExists($name)){
            return Base::getInstance()->getDataProvider()->getData($player->getName(), Base::getInstance()->getDataProvider()->getEntry($name));
        }
    }

    public function modIntEntry(string $entryName, Player $player, int $value = 1)
    {
        if(Base::getInstance()->getDataProvider()->entryExists($entryName)){
            Base::getInstance()->getDataProvider()->incrementValue($player->getName(), Base::getInstance()->getDataProvider()->getEntry($entryName), $value);
        }
    }

    public function setEntry(string $entryName, Player $player, $value)
    {
        if(Base::getInstance()->getDataProvider()->entryExists($entryName)){
            $entry = Base::getInstance()->getDataProvider()->getEntry($entryName);
            switch($entry->getExpectedType())
            {
                case Entry::BOOL:
                    $value = $value=="true"?true:false;
                    break;
                case Entry::INT:
                    $value = (int)$value;
                    break;
                case Entry::FLOAT:
                    $value = (float)$value;
                    break;
                case Entry::ARRAY:
                case Entry::MIXED:
                    echo "/stat test <name> <value> doesn't work with Array and Mixed types";
                    break;
            }
            Base::getInstance()->getDataProvider()->saveData($player->getName(), $entry, $value);
        }
    }

}