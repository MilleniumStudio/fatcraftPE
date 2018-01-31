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
use fatutils\tools\ColorUtils;
use fatutils\tools\schedulers\LoopedExec;
use fatutils\tools\schedulers\Timer;
use pocketmine\level\particle\BlockForceFieldParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector3;

class BarrierParticle extends ShopItem
{
	private $m_MainLoop = null;
	private $m_Circle = null;

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

			if (!($this->m_Circle instanceof CircleAnimation) || !$this->m_Circle->isRunning())
			{
				$l_Level = $this->getEntity()->getLevel();
				$i = 0;
				$this->m_Circle = new CircleAnimation();
				$this->m_Circle
					->setEntity($this->getEntity())
					->setNbPoint(200)
					->setNbSubDivision(6)
					->setRadius(0.8)
					->setTickDuration(40)
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
								    if ($l_Level == null) // this is a hack fix to prevent a crash, i didn't check why it happened
								        return;
									$l_Level->addParticle(new DustParticle($l_Location->add(0, 1.95 + (0.1 * $l_Var), 0), $rVar * $l_Var, $gVar * $l_Var, $bVar * $l_Var));
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