<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 13:53
 */

namespace fatutils\tools;


use fatutils\FatUtils;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\format\Chunk;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\event\Listener;
use pocketmine\event\level\ChunkUnloadEvent;

class WorldUtils
{
    public static $m_ForceLoadedChunks;

	public static function stringToLocation(string $p_RawLoc):Location
	{
		$x = 0.0;
		$y = 0.0;
		$z = 0.0;
		$yaw = 0.0;
		$pitch = 0.0;
		$level = FatUtils::getInstance()->getServer()->getLevel(1);

		$array = explode("/", $p_RawLoc);
		if (count($array) === 3)
		{
			$x = doubleval($array[0]);
			$y = doubleval($array[1]);
			$z = doubleval($array[2]);
		}
		else if (count($array) === 4)
		{
			$level = FatUtils::getInstance()->getServer()->getLevelByName($array[0]);
			$x = doubleval($array[1]);
			$y = doubleval($array[2]);
			$z = doubleval($array[3]);
		} else if (count($array) === 5)
		{
			$x = doubleval($array[0]);
			$y = doubleval($array[1]);
			$z = doubleval($array[2]);
			$yaw = doubleval($array[3]);
			$pitch = doubleval($array[4]);
		} else if (count($array) === 6)
		{
			$level = FatUtils::getInstance()->getServer()->getLevelByName($array[0]);
			$x = doubleval($array[1]);
			$y = doubleval($array[2]);
			$z = doubleval($array[3]);
			$yaw = doubleval($array[4]);
			$pitch = doubleval($array[5]);
		}

		return new Location($x, $y, $z, $yaw, $pitch, $level);
	}

	public static function locationToString(Location $p_Loc):string
	{
		return (is_null($p_Loc->getLevel()) ? "" : $p_Loc->getLevel()->getName() . "/") . $p_Loc->getX() . "/" . $p_Loc->getY() . "/" . $p_Loc->getZ() . "/" . $p_Loc->getYaw() . "/" . $p_Loc->getPitch();
	}

	public static function getRadiusBB(Position $p_Pos, float $p_Radius):AxisAlignedBB {
		return new AxisAlignedBB(
			$p_Pos->getX() - $p_Radius,
			$p_Pos->getY() - $p_Radius,
			$p_Pos->getZ() - $p_Radius,
			$p_Pos->getX() + $p_Radius,
			$p_Pos->getY() + $p_Radius,
			$p_Pos->getZ() + $p_Radius);
	}

	public static function getRelativeBlock(Block $p_Block, int $x, int $y, int $z):Block
	{
            $_Pos = new Position($p_Block->getX() + $x, $p_Block->getY() + $y, $p_Block->getZ() + $z, $p_Block->level);
            self::forceLoadChunk($_Pos);
            $p_Block->getLevel()->getBlock($_Pos)->getId();
            return $p_Block->getLevel()->getBlock($_Pos);
	}

    public static function getDistanceBetween(Position $p_Loc1, Position $p_Loc2):float
    {
        return sqrt(self::getDistanceSquaredBetween($p_Loc1, $p_Loc2));
    }

    public static function getDistanceSquaredBetween(Position $p_Loc1, Position $p_Loc2):float
    {
        return pow($p_Loc1->getX() - $p_Loc2->getX(), 2) + pow($p_Loc1->getY() - $p_Loc2->getY(), 2) + pow($p_Loc1->getZ() - $p_Loc2->getZ(), 2);
    }

	public static function getRandomizedLocation(Location $p_StartLocation, float $p_XBound, float $p_YBound, float $p_ZBound)
    {
        $l_NewX = MathUtils::rand(-$p_XBound, $p_XBound);
        $l_NewY = MathUtils::rand(-$p_YBound, $p_YBound);
        $l_NewZ = MathUtils::rand(-$p_ZBound, $p_ZBound);

        return new Location($p_StartLocation->getX() + $l_NewX, $p_StartLocation->getY() + $l_NewY, $p_StartLocation->getZ() + $l_NewZ, $p_StartLocation->getYaw(), $p_StartLocation->getPitch(), $p_StartLocation->getLevel());
    }

	public static function setBlocksId(array $p_Blocks, int $p_Id)
	{
		foreach ($p_Blocks as $l_Block)
		{
			if ($l_Block instanceof Block)
			{
                $_Pos = new Position($l_Block->getX(), $l_Block->getY(), $l_Block->getZ(), $l_Block->level);
                WorldUtils::loadChunkAt($_Pos);
                $l_Block->getLevel()->setBlock($l_Block, BlockFactory::get($p_Id), true, true);
            }
		}
	}

	public static function loadChunkAt(Position $p_Pos)
    {
            if ($p_Pos->getLevel()->loadChunk($p_Pos->getFloorX() >> 4, $p_Pos->getFloorZ() >> 4))
            {
                FatUtils::getInstance()->getLogger()->debug("Chunk " . ($p_Pos->getFloorX() >> 4) . "/" . ($p_Pos->getFloorZ() >> 4) . " force loaded !");
            }
    }

    public static function isPosInChunk(Location $p_Pos, Chunk $p_Chunk): Boolean
    {
        //!\\ no check for level, Chunk not implement getLevel() method.
        if ($p_Pos->getX() >> 4 == $p_Chunk->getX() && $p_Pos->getZ() >> 4 == $p_Chunk->getZ())
        {
            return true;
        }
        return false;
    }

    public static function forceLoadChunk(Position $p_Pos, bool $load = true)
    {
        $chunk = $p_Pos->getLevel()->getChunk($p_Pos->getFloorX() >> 4, $p_Pos->getFloorZ() >> 4);

        if ($load)
        {
            self::loadChunkAt($p_Pos);
            if (self::$m_ForceLoadedChunks == null)
            {
                self::$m_ForceLoadedChunks = array();
                FatUtils::getInstance()->getServer()->getPluginManager()->registerEvents(new class() implements Listener
                {
                    public function onChunkUnload(ChunkUnloadEvent $p_event)
                    {
                        if (isset(WorldUtils::$m_ForceLoadedChunks[$p_event->getChunk()->getX()][$p_event->getChunk()->getZ()]) && WorldUtils::$m_ForceLoadedChunks[$p_event->getChunk()->getX()][$p_event->getChunk()->getZ()])
                        {
                            $p_event->setCancelled();
//                            FatUtils::getInstance()->getLogger()->debug("Chunk " .$p_event->getChunk()->getX() . "/" . $p_event->getChunk()->getZ() . " cancel unload !");
                        }
                    }
                }, FatUtils::getInstance());
            }
            self::$m_ForceLoadedChunks[$chunk->getX()][$chunk->getZ()] =  true;
        }
        else
        {
            self::$m_ForceLoadedChunks[$chunk->getX()][$chunk->getZ()] = false;
            unset(self::$m_ForceLoadedChunks[$chunk->getX()][$chunk->getZ()]);
        }
    }

    public static function addStrike(Location $p_Loc, $height = 0){

        $level = $p_Loc->getLevel();

        $light = new AddEntityPacket();

        $light->type = 93;
        $light->entityUniqueId = Entity::$entityCount++;
        $light->entityRuntimeId = $light->entityUniqueId;
        $light->metadata = array();
        $light->speedX = 0;
        $light->speedY = 0;
        $light->speedZ = 0;
        $light->yaw = $p_Loc->getYaw();
        $light->pitch = $p_Loc->getPitch();
        $p_Loc2 = $p_Loc;
        $p_Loc2->setComponents($p_Loc->x, $p_Loc->y + $height, $p_Loc->z);
        $light->position = $p_Loc2;

//        $level->addSound(new GenericSound($p_Loc, LevelEventPacket::EVENT_SOUND_CLICK, 1));
        FatUtils::getInstance()->getServer()->broadcastPacket($level->getPlayers(), $light);
    }

    public static function stopWorldsTime()
    {
        foreach (FatUtils::getInstance()->getServer()->getLevels() as $l_Level)
            $l_Level->stopTime();
    }

    public static function setWorldsTime(int $time)
    {
        foreach (FatUtils::getInstance()->getServer()->getLevels() as $l_Level)
            $l_Level->setTime($time);
    }

    public static function getSideFromString($side)
    {
        switch($side){
            case "SIDE_DOWN":
                return Vector3::SIDE_DOWN;
            case "SIDE_UP":
                return Vector3::SIDE_UP;
            case "SIDE_NORTH":
                return Vector3::SIDE_NORTH;
            case "SIDE_SOUTH":
                return Vector3::SIDE_SOUTH;
            case "SIDE_WEST":
                return Vector3::SIDE_WEST;
            case "SIDE_EAST":
                return Vector3::SIDE_EAST;
            default:
                return Vector3::SIDE_DOWN;
        }
    }
}
