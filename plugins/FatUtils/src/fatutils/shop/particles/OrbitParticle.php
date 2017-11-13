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
use fatutils\tools\LoopedExec;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector3;

class OrbitParticle extends ShopItem
{
	private $m_MainLoop = null;
	private $m_Circle1 = null;
	private $m_Circle2 = null;
	private $m_Circle3 = null;

	public function getSlotName(): string
	{
		return ShopItem::SLOT_PARTICLE;
	}

	public function equip()
	{
		$this->m_MainLoop = new LoopedExec(function ()
		{
			$l_Level = $this->getEntity()->getLevel();
			if (!($this->m_Circle1 instanceof CircleAnimation) || !$this->m_Circle1->isRunning())
			{
				$this->m_Circle1 = new CircleAnimation();
				$this->m_Circle1
					->setEntity($this->getEntity())
					->setNbSubDivision(1)
					->setRadius(1.5)
					->setTickDuration(100)
					->setCallback(function ($data) use ($l_Level)
					{
						if (gettype($data) === "array")
						{
							foreach ($data as $l_Location)
							{
								if ($l_Location instanceof Vector3)
								{
									$l_Level->addParticle(new RedstoneParticle($l_Location));
								}
							}
						}
					})
					->play();
			}
			if (!($this->m_Circle2 instanceof CircleAnimation) || !$this->m_Circle2->isRunning())
			{
				$this->m_Circle2 = new CircleAnimation();
				$this->m_Circle2
					->setEntity($this->getEntity())
					->setNbSubDivision(1)
					->setRadius(1.1)
					->setTickDuration(60)
					->setCallback(function ($data) use ($l_Level)
					{
						if (gettype($data) === "array")
						{
							foreach ($data as $l_Location)
							{
								if ($l_Location instanceof Vector3)
								{
									$l_Level->addParticle(new RedstoneParticle($l_Location->add(0, 0.2, 0)));
								}
							}
						}
					})
					->play();
			}
			if (!($this->m_Circle3 instanceof CircleAnimation) || !$this->m_Circle3->isRunning())
			{
				$this->m_Circle3 = new CircleAnimation();
				$this->m_Circle3
					->setEntity($this->getEntity())
					->setNbSubDivision(1)
					->setRadius(0.7)
					->setTickDuration(20)
					->setCallback(function ($data) use ($l_Level)
					{
						if (gettype($data) === "array")
						{
							foreach ($data as $l_Location)
							{
								if ($l_Location instanceof Vector3)
								{
									$l_Level->addParticle(new RedstoneParticle($l_Location->add(0, 0.4, 0)));
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
		if ($this->m_Circle1 instanceof CircleAnimation)
			$this->m_Circle1->stop();
		if ($this->m_Circle2 instanceof CircleAnimation)
			$this->m_Circle2->stop();
		if ($this->m_Circle3 instanceof CircleAnimation)
			$this->m_Circle3->stop();
	}
}