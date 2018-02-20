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
use fatutils\tools\schedulers\LoopedExec;
use fatutils\tools\particles\ParticleBuilder;
use fatutils\tools\schedulers\Timer;
use pocketmine\level\particle\BlockForceFieldParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class PathParticle extends ShopItem
{
	private $m_MainLoop = null;

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
            if ($this->getEntity()->getLevel() == null)
                return;
			if (FatUtils::getInstance()->getServer()->getTick() % 2 == 0 && $this->getEntity()->isMoving())
            {
                $l_ParticleBuilder->play(Position::fromObject($this->getEntity()->asLocation()->add(0, $this->getDataValue("offsetY", 0.1), 0), $this->getEntity()->getLevel()));
                $l_ParticleBuilder->play(Position::fromObject($this->getEntity()->asLocation()->add(0, $this->getDataValue("offsetY", 0.1) + 0.1, 0), $this->getEntity()->getLevel()));
                $l_ParticleBuilder->play(Position::fromObject($this->getEntity()->asLocation()->add($this->getDataValue("offsetY", 0.1) + 0.1, 0, 0), $this->getEntity()->getLevel()));
                $l_ParticleBuilder->play(Position::fromObject($this->getEntity()->asLocation()->add(- $this->getDataValue("offsetY", 0.1) + 0.1, 0, 0), $this->getEntity()->getLevel()));
            }
		});
	}

	public function unequip()
	{
		if ($this->m_MainLoop instanceof LoopedExec)
			$this->m_MainLoop->cancel();
	}
}