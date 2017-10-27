<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 14:11
 */

namespace fatutils\shop\particles;

use fatutils\shop\ShopItem;
use fatutils\tools\ColorUtils;
use fatutils\tools\GeometryUtils;
use fatutils\tools\LoopedExec;
use pocketmine\level\Location;
use pocketmine\level\particle\DustParticle;
use pocketmine\math\Vector3;

/**
 * Class CapeParticle
 * @package fatutils\shop\particles
 *
 * ARGS:
    "rColor" as int
	"gColor" as int
	"bColor" as int
 */
class CapeParticle extends ShopItem
{
	private $m_MainLoop = null;

	private $rColor;
	private $gColor;
	private $bColor;

	public function getSlotName(): string
	{
		return ShopItem::SLOT_PARTICLE;
	}

	public function equip()
	{
		$l_RgbColor = ColorUtils::hexToRgb($this->getDataValue("rgbColor", "#FFFFFF"));
		$this->rColor = $l_RgbColor["r"];
		$this->gColor = $l_RgbColor["g"];
		$this->bColor = $l_RgbColor["b"];

		$this->m_MainLoop = new LoopedExec(function ()
		{
			$l_PlayerPosition = $this->getPlayer()->getLocation();

			$l_PlayerSpeed = $this->getPlayer()->getSpeedVector()->length();

			$l_Positions = [];

			$l_Angle = 180;
			$l_Center = GeometryUtils::relativeToLocation($l_PlayerPosition, 0, 0, 0.5 + ($l_PlayerSpeed * 2));
			$l_base = GeometryUtils::relativeToLocation(Location::fromObject($l_Center, $this->getPlayer()->getLocation()->getLevel(), $this->getPlayer()->getLocation()->getYaw()), 0, $l_Angle, 0.8);

			$l_Positions[] = $l_base;

			for ($i = 0; $i < 5; $i++)
			{
				$l_Angle -= 6;
				$l_Positions[] = GeometryUtils::relativeToLocation(Location::fromObject($l_Center, $this->getPlayer()->getLocation()->getLevel(), $this->getPlayer()->getLocation()->getYaw()), 0, $l_Angle, 0.8);
			}

			$l_Angle = 180;
			for ($i = 0; $i < 5; $i++)
			{
				$l_Angle += 6;
				$l_Positions[] = GeometryUtils::relativeToLocation(Location::fromObject($l_Center, $this->getPlayer()->getLocation()->getLevel(), $this->getPlayer()->getLocation()->getYaw()), 0, $l_Angle, 0.8);
			}

			$l_Level = $l_PlayerPosition->getLevel();
			foreach ($l_Positions as $l_Pos)
			{
				if ($l_Pos instanceof Vector3)
					$l_Level->addParticle(new DustParticle($l_Pos->add(0, ($this->getPlayer()->isSneaking() ? 1.30 : 1.50)), $this->rColor, $this->gColor, $this->bColor));
			}
		}, 2);
	}

	public function unequip()
	{
		if ($this->m_MainLoop instanceof LoopedExec)
			$this->m_MainLoop->cancel();
	}
}