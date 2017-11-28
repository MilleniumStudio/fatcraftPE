<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 07/11/2017
 * Time: 11:17
 */

namespace fatutils\powers;


use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

abstract class APower
{
    /** @var Player $owner */
    protected $owner;
    private $uniqueId;

    public function __construct(Player $owner)
    {
        $this->owner = $owner;
        $this->uniqueId = uniqid();
        // add item
        $item = $this->getIcon();
        $tags = $item->getNamedTag() ?? new CompoundTag("",[]);
        $tags->powerKey = new StringTag("powerKey", $this->uniqueId);
        $item->setNamedTag($tags);
        $owner->getInventory()->setItem(PowersManager::SLOT, $item);
		$owner->getInventory()->setHeldItemIndex(PowersManager::SLOT);
    }
    public function getUniqueId():string {
        return $this->uniqueId;
    }
    public function destroy(){
        foreach ($this->owner->getInventory()->getContents() as $item){
            if($item instanceof Item){
                if(isset($item->getNamedTag()->powerKey) && $item->getNamedTag()->powerKey == $this->uniqueId){
                    $this->owner->getInventory()->remove($item);
                    return;
                }
            }
        }
    }
    abstract function getIcon(): Item;
    abstract function action(): bool;

}