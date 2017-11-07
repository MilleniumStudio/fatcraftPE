<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 03/11/2017
 * Time: 10:32
 */

namespace fatutils\tools\volume;

use fatutils\FatUtils;
use fatutils\tools\MathUtils;
use fatutils\tools\particles\ParticleBuilder;
use fatutils\tools\WorldUtils;
use pocketmine\level\particle\Particle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class CuboidVolume extends Volume
{
	/** @var Position */
	private $m_Pos1;
	/** @var Position */
	private $m_Pos2;

	private $m_MinX = 0;
	private $m_MinY = 0;
	private $m_MinZ = 0;
	private $m_MaxX = 0;
	private $m_MaxY = 0;
	private $m_MaxZ = 0;

	//--> BLOCKS
	private $m_EdgeBlocks = null;
	private $m_CornerBlocks = null;

	public static function createRelativeVolume(Position $p_RelativPos, float $p_RelPos1X = 0, float $p_RelPos1Y = 0, float $p_RelPos1Z = 0, float $p_RelPos2X = 0, float $p_RelPos2Y = 0, float $p_RelPos2Z = 0): CuboidVolume
	{
		$l_Pos1 = Position::fromObject($p_RelativPos->add($p_RelPos1X, $p_RelPos1Y, $p_RelPos1Z), $p_RelativPos->getLevel());
		$l_Pos2 = Position::fromObject($p_RelativPos->add($p_RelPos2X, $p_RelPos2Y, $p_RelPos2Z), $p_RelativPos->getLevel());

		return new CuboidVolume($l_Pos1, $l_Pos2);
	}

	/**
	 *
	 * [
	 *    	"relPosition" => "0/0/0", // as x/y/z
	 *    	"relExpansion" => [
	 *			"-x" => 0,
	 *			"x" => 0,
	 *			"-y" => 0,
	 *			"y" => 0,
	 *			"-z" => 0,
	 *			"z" => 0,
	 * 	 	]
	 *	 	"relExpansion" => 1.5
	 * or
	 *    	"pos1" => "0/0/0", // as x/y/z
	 *    	"pos2" => "0/0/0" // as x/y/z
	 * ]
	 *
	 * @param array $p_VolumeConfig
	 * @return CuboidVolume
	 */
	public static function createVolumeFromConfig(array $p_VolumeConfig): ?CuboidVolume
	{
		$l_Ret = null;

		if (array_key_exists("relPosition", $p_VolumeConfig))
		{
			if (array_key_exists("relExpansion", $p_VolumeConfig))
			{
				$l_RelPos1X = 0;
				$l_RelPos1Y = 0;
				$l_RelPos1Z = 0;
				$l_RelPos2X = 0;
				$l_RelPos2Y = 0;
				$l_RelPos2Z = 0;

				if (is_array($p_VolumeConfig["relExpansion"]))
				{
					if (array_key_exists("-x", $p_VolumeConfig["relExpansion"]))
						$l_RelPos1X = $p_VolumeConfig["relExpansion"]["-x"];
					if (array_key_exists("x", $p_VolumeConfig["relExpansion"]))
						$l_RelPos2X = $p_VolumeConfig["relExpansion"]["x"];
					if (array_key_exists("-y", $p_VolumeConfig["relExpansion"]))
						$l_RelPos1Y = $p_VolumeConfig["relExpansion"]["-y"];
					if (array_key_exists("y", $p_VolumeConfig["relExpansion"]))
						$l_RelPos2Y = $p_VolumeConfig["relExpansion"]["y"];
					if (array_key_exists("-z", $p_VolumeConfig["relExpansion"]))
						$l_RelPos1Z = $p_VolumeConfig["relExpansion"]["-z"];
					if (array_key_exists("z", $p_VolumeConfig["relExpansion"]))
						$l_RelPos2Z = $p_VolumeConfig["relExpansion"]["z"];

				} else if (is_numeric($p_VolumeConfig["relExpansion"]))
				{
					$l_RelPos1X = $l_RelPos1Y = $l_RelPos1Z = $p_VolumeConfig["relExpansion"];
					$l_RelPos2X = $l_RelPos2Y = $l_RelPos2Z = -$p_VolumeConfig["relExpansion"];
					var_dump($l_RelPos1X, $l_RelPos1Y, $l_RelPos1Z, $l_RelPos2X, $l_RelPos2Y, $l_RelPos2Z);
				}

				$l_Ret = self::createRelativeVolume(WorldUtils::stringToLocation($p_VolumeConfig["relPosition"]), $l_RelPos1X, $l_RelPos1Y, $l_RelPos1Z, $l_RelPos2X, $l_RelPos2Y, $l_RelPos2Z);
			}
		} else if (array_key_exists("pos1", $p_VolumeConfig) && array_key_exists("pos2", $p_VolumeConfig))
			$l_Ret = new CuboidVolume(WorldUtils::stringToLocation($p_VolumeConfig["pos1"]), WorldUtils::stringToLocation($p_VolumeConfig["pos2"]));
		else
			var_dump("Unknown key in volum config", $p_VolumeConfig);

		return $l_Ret;
	}

	public function __construct(Position $p_Pos1, Position $p_Pos2)
	{
		parent::__construct($p_Pos1->getLevel());

		$this->m_Pos1 = $p_Pos1;
		$this->m_Pos2 = $p_Pos2;

		$this->m_MinX = ($p_Pos1->getX() < $p_Pos2->getX()) ? $p_Pos1->getX() : $p_Pos2->getX();
		$this->m_MinY = ($p_Pos1->getY() < $p_Pos2->getY()) ? $p_Pos1->getY() : $p_Pos2->getY();
		$this->m_MinZ = ($p_Pos1->getZ() < $p_Pos2->getZ()) ? $p_Pos1->getZ() : $p_Pos2->getZ();
		$this->m_MaxX = ($p_Pos1->getX() < $p_Pos2->getX()) ? $p_Pos2->getX() : $p_Pos1->getX();
		$this->m_MaxY = ($p_Pos1->getY() < $p_Pos2->getY()) ? $p_Pos2->getY() : $p_Pos1->getY();
		$this->m_MaxZ = ($p_Pos1->getZ() < $p_Pos2->getZ()) ? $p_Pos2->getZ() : $p_Pos1->getZ();
	}

	/**
	 * @param int $p_Quantity
	 * @return Position[]
	 */
	public function getRandomLocations(int $p_Quantity = 1): array
	{
		$l_Ret = [];

		for ($i = 0; $i < $p_Quantity; $i++)
			$l_Ret[] = new Position(MathUtils::frand($this->getMinX(), $this->getMaxX(), 2), MathUtils::frand($this->getMinY(), $this->getMaxY(), 2), MathUtils::frand($this->getMinZ(), $this->getMaxZ(), 2), $this->getPos1()->getLevel());

		return $l_Ret;
	}

	public function isIn(Vector3 $p_Position): bool
	{
		return ($p_Position->x >= $this->m_MinX) && ($p_Position->x < $this->m_MaxX) && ($p_Position->y >= $this->m_MinY) && ($p_Position->y < $this->m_MaxY) && ($p_Position->z >= $this->m_MinZ) && ($p_Position->z < $this->m_MaxZ);
	}

	public function display()
	{
		$l_Edges = ParticleBuilder::fromParticleId(Particle::TYPE_REDSTONE);

		$l_Edges->play(Position::fromObject($this->getPos1(), $this->getLevel()));
		$l_Edges->play(Position::fromObject($this->getPos2(), $this->getLevel()));

		$l_RandomParticle = ParticleBuilder::fromParticleId(Particle::TYPE_DUST)->setColor(10, 10, 210);
		foreach ($this->getRandomLocations(5) as $l_Position)
			$l_RandomParticle->play($l_Position);
	}

	public function computeBlocks()
	{
		$this->m_Blocks = [];
		$this->m_ExteriorBlocks = [];
		$this->m_InteriorBlocks = [];
		$this->m_CornerBlocks = [];
		$this->m_EdgeBlocks = [];

		$l_Level = $this->getPos1()->getLevel();

		// CORNERS
		$this->m_CornerBlocks[] = $l_Level->getBlock(new Position($this->getMinX(), $this->getMinY(), $this->getMinZ(), $l_Level));
		$this->m_CornerBlocks[] = $l_Level->getBlock(new Position($this->getMinX(), $this->getMinY(), $this->getMaxZ(), $l_Level));
		$this->m_CornerBlocks[] = $l_Level->getBlock(new Position($this->getMaxX(), $this->getMinY(), $this->getMinZ(), $l_Level));
		$this->m_CornerBlocks[] = $l_Level->getBlock(new Position($this->getMaxX(), $this->getMinY(), $this->getMaxZ(), $l_Level));
		$this->m_CornerBlocks[] = $l_Level->getBlock(new Position($this->getMinX(), $this->getMaxY(), $this->getMinZ(), $l_Level));
		$this->m_CornerBlocks[] = $l_Level->getBlock(new Position($this->getMinX(), $this->getMaxY(), $this->getMaxZ(), $l_Level));
		$this->m_CornerBlocks[] = $l_Level->getBlock(new Position($this->getMaxX(), $this->getMaxY(), $this->getMinZ(), $l_Level));
		$this->m_CornerBlocks[] = $l_Level->getBlock(new Position($this->getMaxX(), $this->getMaxY(), $this->getMaxZ(), $l_Level));

		// EXTERIOR / INTERIORS
		for ($i = $this->getMinX(); $i <= $this->getMaxX(); $i++)
		{
			for ($j = $this->getMinY(); $j <= $this->getMaxY(); $j++)
			{
				for ($k = $this->getMinZ(); $k <= $this->getMaxZ(); $k++)
				{
					if (($k == $this->getMinZ()) || ($k == $this->getMaxZ()) || ($j == $this->getMinY()) || ($j == $this->getMaxY()) || ($i == $this->getMinX()) || ($i == $this->getMaxX()))
					{
						// EDGES
						if (($i == $this->getMinX() || $i == $this->getMaxX()) || ($j == $this->getMinY() || $j == $this->getMaxY()))
						{
							$this->m_EdgeBlocks[] = $l_Level->getBlock(new Position($i, $j, $this->getMinZ(), $l_Level));
							$this->m_EdgeBlocks[] = $l_Level->getBlock(new Position($i, $j, $this->getMaxZ(), $l_Level));
						}
						if (($k == $this->getMinZ() || $k == $this->getMaxZ()) || ($j == $this->getMinY() || $j == $this->getMaxY()))
						{
							$this->m_EdgeBlocks[] = $l_Level->getBlock(new Position($this->getMinX(), $j, $k, $l_Level));
							$this->m_EdgeBlocks[] = $l_Level->getBlock(new Position($this->getMaxX(), $j, $k, $l_Level));
						}

						$this->m_ExteriorBlocks[] = $l_Level->getBlock(new Position($i, $j, $k, $l_Level));
					} else
						$this->m_InteriorBlocks[] = $l_Level->getBlock(new Position($i, $j, $k, $l_Level));
				}
			}
		}

		$this->m_Blocks = array_merge($this->m_ExteriorBlocks, $this->m_InteriorBlocks);
	}

	/**
	 * @return Position
	 */
	public
	function getPos1(): Position
	{
		return $this->m_Pos1;
	}

	/**
	 * @return Position
	 */
	public
	function getPos2(): Position
	{
		return $this->m_Pos2;
	}

	/**
	 * @return float
	 */
	public
	function getMinX(): float
	{
		return $this->m_MinX;
	}

	/**
	 * @return float
	 */
	public
	function getMinY(): float
	{
		return $this->m_MinY;
	}

	/**
	 * @return float
	 */
	public
	function getMinZ(): float
	{
		return $this->m_MinZ;
	}

	/**
	 * @return float
	 */
	public
	function getMaxX(): float
	{
		return $this->m_MaxX;
	}

	/**
	 * @return float
	 */
	public
	function getMaxY(): float
	{
		return $this->m_MaxY;
	}

	/**
	 * @return float
	 */
	public
	function getMaxZ(): float
	{
		return $this->m_MaxZ;
	}
}