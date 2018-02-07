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
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class GlyphParticle extends ShopItem
{
	private $m_MainLoop = null;
	private $m_Anim = null;

	public function getSlotName(): string
	{
		return ShopItem::SLOT_PARTICLE;
	}

	public function equip()
	{
		$l_RawParticle = $this->getDataValue("rawParticle", null);
		$l_ParticleBuilder = is_null($l_RawParticle) ? ParticleBuilder::fromParticleId(Particle::TYPE_ENCHANTMENT_TABLE) : ParticleBuilder::fromRaw($l_RawParticle);

		$this->m_MainLoop = new LoopedExec(function () use ($l_ParticleBuilder)
		{
			if (FatUtils::getInstance()->getServer()->getTick() % 70 == 0)
			{
				if (!($this->m_Anim instanceof ShockWaveAnimation) || !$this->m_Anim->isRunning())
				{
					$l_Level = $this->getEntity()->getLevel();
					$this->m_Anim = new ShockWaveAnimation($this->getEntity()->asLocation());
					$this->m_Anim
						->setNbPointInACircle(10)
						->setTickDuration(20 * 2)
						->setFinalRadius(2)
						->setCallback(function ($data) use ($l_Level, $l_ParticleBuilder)
						{
                            $l_Level = $this->getEntity()->getLevel();
							if (gettype($data) === "array")
							{
								foreach ($data as $l_Location)
								{
									if ($l_Location instanceof Vector3)
									{
                                        if ($l_Level == null) // this is a hack fix to prevent a crash, i didn't check why it happened
                                            return;
										$l_ParticleBuilder->play(Position::fromObject($l_Location->add(0, $this->getDataValue("offsetY", 0.1), 0), $l_Level));
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