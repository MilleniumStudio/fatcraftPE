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
use fatutils\tools\schedulers\LoopedExec;
use fatutils\tools\particles\ParticleBuilder;
use fatutils\tools\schedulers\Timer;
use pocketmine\level\particle\BlockForceFieldParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\Particle;
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
		$this->m_MainLoop = new LoopedExec(function ()
		{
			if (FatUtils::getInstance()->getServer()->getTick() % 3 == 0 && $this->getEntity()->isMoving())
			{
                $l_Level = $this->getEntity()->getLevel();

                $this->m_Anim = new ShockWaveAnimation($this->getEntity()->asLocation());
				$this->m_Anim
					->setNbPointInACircle(10)
					->setTickDuration(15)
					->setStartRadius(0.3)
					->setFinalRadius(0.5)
					->setCallback(function ($data) use ($l_Level)
					{
						if (gettype($data) === "array")
						{
							foreach ($data as $l_Location)
							{
								if ($l_Location instanceof Vector3)
								{
                                    if ($l_Level == null) // this is a hack fix to prevent a crash, i didn't check why it happened
                                        return;
                                    $l_Level->addParticle(new EnchantmentTableParticle($l_Location->add(0, 0.1, 0)));
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