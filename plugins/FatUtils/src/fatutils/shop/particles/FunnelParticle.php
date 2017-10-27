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
use fatutils\tools\ColorUtils;
use fatutils\tools\LoopedExec;
use fatutils\tools\Timer;
use pocketmine\level\particle\BlockForceFieldParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector3;

class FunnelParticle extends ShopItem
{
	private $m_MainLoop = null;
	private $m_Anim = null;

	public function getSlotName(): string
	{
		return ShopItem::SLOT_PARTICLE;
	}

	public function equip()
	{
		$l_RgbColor = ColorUtils::hexToRgb($this->getDataValue("rgbColor", "#FFFFFF"));
		$rVar = $l_RgbColor["r"];
		$gVar = $l_RgbColor["g"];
		$bVar = $l_RgbColor["b"];

		$l_ShouldVary = $this->getDataValue("randomColors", false);

		$this->m_MainLoop = new LoopedExec(function () use ($l_ShouldVary, &$rVar, &$gVar, &$bVar)
		{
			if ($l_ShouldVary && FatUtils::getInstance()->getServer()->getTick() % 20 == 0)
			{
				$rVar = rand(0, 255);
				$gVar = rand(0, 255);
				$bVar = rand(0, 255);
			}

			if (FatUtils::getInstance()->getServer()->getTick() % 70 == 0)
			{
				if (!($this->m_Anim instanceof ShockWaveAnimation) || !$this->m_Anim->isRunning())
				{
					$l_Level = $this->getPlayer()->getLevel();
					$i = 0;
					$this->m_Anim = new ShockWaveAnimation($this->getPlayer()->asLocation());
					$this->m_Anim
						->setNbPointInACircle(15)
						->setTickDuration(20 * 2)
						->setFinalRadius(1.7)
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
										$l_Level->addParticle(new DustParticle($l_Location->add(0, $this->getDataValue("offsetY", 1.95) + (0.1 * $l_Var), 0), $rVar * $l_Var, $gVar * $l_Var, $bVar * $l_Var));
									}
								}
							}
						})
						->play();
				}
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