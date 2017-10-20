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
use pocketmine\level\particle\BlockForceFieldParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\RedstoneParticle;
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
		$this->m_MainLoop = new LoopedExec(function ()
		{
			if (!($this->m_Circle instanceof CircleAnimation) || !$this->m_Circle->isRunning())
			{
				$l_Level = $this->getPlayer()->getLevel();
				$i = 0;
				$this->m_Circle = new CircleAnimation();
				$this->m_Circle
					->setEntity($this->getPlayer())
					->setNbPoint(500)
					->setNbSubDivision(5)
					->setRadius(0.5)
					->setTickDuration(100)
					->setCallback(function ($data) use ($l_Level, &$i)
					{
						if (gettype($data) === "array")
						{
							$l_Var = (0.15 * sin($i));
							$i += 0.1;
							foreach ($data as $l_Location)
							{
								if ($l_Location instanceof Vector3)
								{
									$l_Level->addParticle(new FlameParticle($l_Location->add(0, 2 + $l_Var, 0)));
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