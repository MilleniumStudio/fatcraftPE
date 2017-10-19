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
use fatutils\tools\Timer;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector3;

class ParticleItem extends ShopItem
{
	private $m_MainLoop = null;
	private $m_Circle = null;

	public function getSlotName(): string
	{
		return ShopItem::SLOT_PARTICLE;
	}

	public function equip()
	{
		$this->m_MainLoop = new LoopedExec(function ()
		{
			if (!($this->m_Circle instanceof CircleAnimation) || !$this->m_Circle->isRunning())
			{
				$l_Level = $this->getPlayer()->getLevel();
				$this->m_Circle = new CircleAnimation();
				$this->m_Circle
					->setEntity($this->getPlayer())
					->setNbPoint(500)
					->setNbSubDivision(5)
					->setRadius(2)
					->setTickDuration(100)
					->setCallback(function ($data) use ($l_Level)
					{
						if (gettype($data) === "array")
						{
							foreach ($data as $l_Location)
							{
								if ($l_Location instanceof Vector3)
								{
									$l_Level->addParticle(new RedstoneParticle($l_Location->add(0, 1.90, 0)));
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