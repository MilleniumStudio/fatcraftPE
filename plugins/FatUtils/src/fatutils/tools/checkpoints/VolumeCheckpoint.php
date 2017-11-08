<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 06/11/2017
 * Time: 14:40
 */

namespace fatutils\tools\checkpoints;

use fatutils\tools\particles\ParticleBuilder;
use fatutils\tools\volume\Volume;
use pocketmine\entity\Entity;
use pocketmine\level\particle\Particle;
use pocketmine\Player;

class VolumeCheckpoint extends Checkpoint
{
	private $m_CollisionVolume = null;

	public function __construct(Volume $p_CollisionVolume)
	{
		parent::__construct();
		$this->m_CollisionVolume = $p_CollisionVolume;

		$this->m_CollisionVolume
			->addEnteringListener(function (Entity $p_Entity)
			{
				if ($this->getCheckpointsPath()->isEnabled())
				{
					if ($p_Entity instanceof Player)
						$this->notifyCollision($p_Entity);
				} else
					$this->m_CollisionVolume->resetEntityInside($p_Entity);
			});
	}

	public function getCollisionVolume(): Volume
	{
		return $this->m_CollisionVolume;
	}

	public function displayAsNext(Player $p_Player = null)
	{
		if ($this->isStart())
		{
			$l_RandomParticle = ParticleBuilder::fromParticleId(Particle::TYPE_DUST)->setColor(10, 10, 240);
			foreach ($this->getCollisionVolume()->getRandomLocations(7) as $l_Location)
				$l_RandomParticle->playForPlayer($l_Location, $p_Player);
		} else if ($this->isEnd())
		{
			$l_RandomParticle = ParticleBuilder::fromParticleId(Particle::TYPE_VILLAGER_HAPPY);
			foreach ($this->getCollisionVolume()->getRandomLocations(10) as $l_Location)
				$l_RandomParticle->playForPlayer($l_Location, $p_Player);
		} else
		{
			$l_RandomParticle = ParticleBuilder::fromParticleId(Particle::TYPE_LAVA);
			foreach ($this->getCollisionVolume()->getRandomLocations(4) as $l_Location)
				$l_RandomParticle->playForPlayer($l_Location, $p_Player);
		}
	}

	public function displayAsAfterNext(Player $p_Player = null)
	{
		$l_RandomParticle = ParticleBuilder::fromParticleId(Particle::TYPE_END_ROD);
		foreach ($this->getCollisionVolume()->getRandomLocations(5) as $l_Position)
			$l_RandomParticle->playForPlayer($l_Position, $p_Player);
	}
}