<?php
/**
 * Created by IntelliJ IDEA.
 * User: Unikaz
 * Date: 09/11/2017
 * Time: 10:59
 */

namespace fatutils\powers\effects;


use fatutils\powers\APower;
use fatutils\tools\schedulers\DelayedExec;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

class Mine extends APower
{

    function getIcon(): Item
    {
        return \pocketmine\item\ItemFactory::get(49);
    }

    function action(): bool
    {
        $this->destroyItem();
        /** @var Block[] $blocks */
        $blocks = [];
        $loc = $this->owner->asVector3()->add(new Vector3(-cos(deg2rad($this->owner->vehicle->yaw)) * 3, 0, -sin(deg2rad($this->owner->vehicle->yaw)) * 3));
        for ($i = 0; $i < 3; $i++) {
            $x = rand(-1, 1);
            $z = rand(-1, 1);
            $loc2 = $loc->add(new Vector3($x, -1, $z))->floor();
            $block = $this->owner->level->getBlockAt($loc2->x, $loc2->y, $loc2->z);
            if ($block->getId() == BlockIds::AIR) {
                $blocks[] = $block;
                $this->owner->level->setBlockIdAt($loc2->x, $loc2->y, $loc2->z, BlockIds::OBSIDIAN);
            }
        }
        new DelayedExec(function () use ($blocks) {
            foreach ($blocks as $block) {
                $this->owner->level->setBlockIdAt($block->x, $block->y, $block->z, BlockIds::AIR);
            }
        }, 20*5);
        return true;
    }
}