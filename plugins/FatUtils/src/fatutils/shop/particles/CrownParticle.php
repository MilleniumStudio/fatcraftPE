<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 14:11
 */

namespace fatutils\shop\particles;

use fatutils\shop\ShopItem;
use fatutils\tools\animations\CircleAnimation;
use fatutils\tools\schedulers\LoopedExec;
use fatutils\tools\particles\ParticleBuilder;
use pocketmine\level\particle\Particle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class CrownParticle extends ShopItem
{
	private $m_MainLoop = null;
	private $m_Circle = null;

	public function getSlotName(): string
	{
		return ShopItem::SLOT_PARTICLE;
	}

	public function equip()
	{
		$l_RawParticle = $this->getDataValue("rawParticle", null);
		$l_ParticleBuilder = is_null($l_RawParticle) ? ParticleBuilder::fromParticleId(Particle::TYPE_FLAME) : ParticleBuilder::fromRaw($l_RawParticle);

		$this->m_MainLoop = new LoopedExec(function () use ($l_ParticleBuilder)
		{
			if (!($this->m_Circle instanceof CircleAnimation) || !$this->m_Circle->isRunning())
			{
				$l_Level = $this->getEntity()->getLevel();
				$i = 0;
				$this->m_Circle = new CircleAnimation();
				$this->m_Circle
					->setEntity($this->getEntity())
					->setNbPoint(500)
					->setNbSubDivision(5)
					->setRadius(0.5)
					->setTickDuration(100)
					->setCallback(function ($data) use ($l_ParticleBuilder, $l_Level, &$i)
					{
						if (gettype($data) === "array")
						{
							$l_Var = (0.15 * sin($i));
							$i += 0.1;
							foreach ($data as $l_Location)
							{
								if ($l_Location instanceof Vector3)
								{
									$l_ParticleBuilder->play(Position::fromObject($l_Location->add(0, 2 + $l_Var, 0), $this->getEntity()->getLevel()));
								}
							}
						}
					})
					->play();
			}
		});
	}

	public function unequip()
	{
		if ($this->m_MainLoop instanceof LoopedExec)
			$this->m_MainLoop->cancel();
		if ($this->m_Circle instanceof CircleAnimation)
			$this->m_Circle->stop();
	}
}