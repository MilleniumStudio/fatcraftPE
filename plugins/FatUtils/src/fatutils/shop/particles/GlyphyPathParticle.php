<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 14:11
 */

namespace fatutils\shop\particles;

use fatutils\FatUtils;
use fatutils\shop\ShopItem;
use fatutils\tools\animations\CircleAnimation;
use fatutils\tools\animations\ShockWaveAnimation;
use fatutils\tools\LoopedExec;
use fatutils\tools\Timer;
use pocketmine\level\particle\BlockForceFieldParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector3;

class GlyphyPathParticle extends ShopItem
{
	private $m_MainLoop = null;
	private $m_Anim = null;

	public function getSlotName(): string
	{
		return ShopItem::SLOT_PARTICLE;
	}

	public function equip()
	{
		$rVar = $this->getDataValue("rColor", 255);
		$gVar = $this->getDataValue("gColor", 255);
		$bVar = $this->getDataValue("bColor", 255);

		$l_ShouldVary = $this->getDataValue("randomColors", false);

		$this->m_MainLoop = new LoopedExec(function () use ($l_ShouldVary, &$rVar, &$gVar, &$bVar)
		{
			if ($l_ShouldVary && FatUtils::getInstance()->getServer()->getTick() % 20 == 0)
			{
				$rVar = rand(0, 255);
				$gVar = rand(0, 255);
				$bVar = rand(0, 255);
			}

			if (FatUtils::getInstance()->getServer()->getTick() % 2 == 0 && $this->getPlayer()->isMoving())
			{
				$l_Level = $this->getPlayer()->getLevel();
				$i = 0;
				$this->m_Anim = new ShockWaveAnimation($this->getPlayer()->asLocation());
				$this->m_Anim
					->setNbPointInACircle(10)
					->setTickDuration(20)
					->setStartRadius(0.1)
					->setFinalRadius(0.7)
					->setCallback(function ($data) use ($l_ShouldVary, $l_Level, &$i, &$rVar, &$gVar, &$bVar)
					{
						if (gettype($data) === "array")
						{
							$l_Var = ($l_ShouldVary ? sin($i) : 1);
							$i += 0.1;
							foreach ($data as $l_Location)
							{
								if ($l_Location instanceof Vector3)
								{
									$l_Level->addParticle(new EnchantmentTableParticle($l_Location->add(0, $this->getDataValue("offsetY", 1.95) + (0.1 * $l_Var), 0)));
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
		if ($this->m_Anim instanceof CircleAnimation)
			$this->m_Anim->stop();
	}
}