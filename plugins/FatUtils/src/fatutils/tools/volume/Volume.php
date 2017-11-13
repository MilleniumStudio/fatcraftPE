<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 06/11/2017
 * Time: 14:04
 */

namespace fatutils\tools\volume;


use fatutils\tools\LoopedExec;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\TimingsHandler;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\math\Vector3;

abstract class Volume
{
	protected static $m_IdAutoIncrement = 0;
	protected $m_Id;

	protected $m_Level;

	protected $m_CollisionCallbacks = [];
	protected $m_EnterVolumeCallbacks = [];
	protected $m_LeaveVolumeCallbacks = [];

	/** @var Entity[] */
	protected $m_EntitiesInside = [];

	/** @var LoopedExec */
	protected static $m_CollisionTask = null;

	/** @var Volume[] */
	protected static $m_CollidableVolumes = [];
	protected $m_Timings = null;

	//--> BLOCKS
	protected $m_Blocks = null;
	protected $m_ExteriorBlocks = null;
	protected $m_InteriorBlocks = null;

	/**
	 * Volume constructor.
	 * @param $m_Level
	 */
	public function __construct(Level $m_Level)
	{
		$this->m_Id = self::$m_IdAutoIncrement++;
		$this->m_Level = $m_Level;
	}

	//-----------------------
	// ABSTRACT
	//-----------------------
	/**
	 * @param int $p_Quantity
	 * @return Location[]
	 */
	public abstract function getRandomLocations(int $p_Quantity = 1): array;

	public abstract function isIn(Vector3 $p_Position): bool;

	public abstract function display();

	public abstract function computeBlocks();

	/** @return Block[] */
	public function getBlocks(): array
	{
		if ($this->m_Blocks == null)
			$this->computeBlocks();

		return $this->m_Blocks;
	}

	//-----------------------
	// UTILS
	//-----------------------
	/**
	 * @param callable(Entity) $p_CollisionCallback
	 * @return Volume
	 */
	public function addCollisionListener(Callable $p_CollisionCallback):Volume
	{
		$this->m_CollisionCallbacks[] = $p_CollisionCallback;
		$this->initScheduler();
		return $this;
	}

	/**
	 * @param callable (Entity) $p_EnteringCallback
	 * @return Volume
	 */
	public function addEnteringListener(Callable $p_EnteringCallback):Volume
	{
		$this->m_EnterVolumeCallbacks[] = $p_EnteringCallback;
		$this->initScheduler();
		return $this;
	}

	/**
	 * @param callable(Entity) $p_CollisionCallback
	 * @return Volume
	 */
	public function addLeavingListener(Callable $p_LeavingCallback):Volume
	{
		$this->m_LeaveVolumeCallbacks[] = $p_LeavingCallback;
		$this->initScheduler();
		return $this;
	}

	/**
	 * @return Entity[]
	 */
	public function getEntityInside(): array
	{
		$l_Ret = [];

		foreach ($this->getLevel()->getEntities() as $l_Entity)
			if ($this->isIn($l_Entity))
				$lret[] = $l_Entity;

		return $l_Ret;
	}

	/**
	 * @param Entity|null $p_Entity if null, all entities are reset
	 */
	public function resetEntityInside(?Entity $p_Entity = null)
	{
		if ($p_Entity == null)
			$this->m_EntitiesInside = [];
		else
		{
			if (array_key_exists($p_Entity->getId(), $this->m_EntitiesInside))
				unset($this->m_EntitiesInside[$p_Entity->getId()]);
		}
	}

	public function removeListeners()
	{
		if (array_key_exists($this->m_Id, self::$m_CollidableVolumes))
		{
			if (count(self::$m_CollidableVolumes) == 1)
			{
				self::$m_CollisionTask->cancel();
				self::$m_CollisionTask = null;
			}

			$this->m_CollisionCallbacks = [];
			$this->m_EnterVolumeCallbacks = [];
			$this->m_LeaveVolumeCallbacks = [];

			unset(self::$m_CollidableVolumes[$this->m_Id]);
		}
	}

	//-----------------------
	// INTERNAL
	//-----------------------
	public function initScheduler()
	{
		if (array_key_exists($this->m_Id, self::$m_CollidableVolumes) == false)
		{
			self::$m_CollidableVolumes[$this->m_Id] = $this;
		}

		if (is_null(self::$m_CollisionTask))
		{
			self::$m_CollisionTask = new LoopedExec(function () {

				if (is_null($this->m_Timings))
					$this->m_Timings = new TimingsHandler("VolumesCollide");

				$this->m_Timings->startTiming();
				foreach (self::$m_CollidableVolumes as $l_Volume)
				{
					foreach ($l_Volume->getLevel()->getEntities() as $l_Entity)
					{
						$l_WasIn = array_key_exists($l_Entity->getId(), $l_Volume->m_EntitiesInside);

						if ($l_Volume->isIn($l_Entity))
						{
							if (!$l_WasIn)
							{
								$l_Volume->m_EntitiesInside[$l_Entity->getId()] = $l_Entity;

								// ENTERING
								foreach ($l_Volume->m_EnterVolumeCallbacks as $l_EnterVolumeCallback)
								{
									if (is_callable($l_EnterVolumeCallback))
										$l_EnterVolumeCallback($l_Entity);
								}
							}

							// COLLISIONS
							foreach ($l_Volume->m_CollisionCallbacks as $l_CollisionCallback)
							{
								if (is_callable($l_CollisionCallback))
									$l_CollisionCallback($l_Entity);
							}
						} else if ($l_WasIn)
						{
							unset($l_Volume->m_EntitiesInside[$l_Entity->getId()]);

							// LEAVING
							foreach ($l_Volume->m_LeaveVolumeCallbacks as $l_LeaveVolumeCallback)
							{
								if (is_callable($l_LeaveVolumeCallback))
									$l_LeaveVolumeCallback($l_Entity);
							}
						}
					}
				}
				$this->m_Timings->stopTiming();
			});
		}
	}

	//-----------------------
	// GETTERS
	//-----------------------
	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->m_Id;
	}

	/**
	 * @return Level
	 */
	public function getLevel(): Level
	{
		return $this->m_Level;
	}
}