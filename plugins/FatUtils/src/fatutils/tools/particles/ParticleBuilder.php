<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 26/10/2017
 * Time: 15:10
 */

namespace fatutils\tools\particles;


use fatutils\FatUtils;
use fatutils\tools\ArrayUtils;
use fatutils\tools\ColorUtils;
use fatutils\tools\ItemUtils;
use pocketmine\level\particle\Particle;
use pocketmine\level\Position;
use pocketmine\Player;
use ReflectionClass;

class ParticleBuilder
{
	private static $m_ParticleNames = null;

	private $m_Particle = null;
	private $m_Id = Particle::TYPE_REDSTONE;
	private $m_Data = 0;

	const KEY_NAME = "particleName"; // as String (see pocketmine\level\particle\Particle)
	const KEY_DATA = "data"; // as int

	const KEY_SCALE = "scale"; // as int
	const KEY_COLOR = "color"; // as string (like "#FFFFFF")
	const KEY_ITEM_NAME = "itemName"; // as String (see pocketmine\item\ItemIds)

	public static function fromParticleId(int $p_ParticleId):ParticleBuilder
	{
		$l_Ret = new ParticleBuilder();
		$l_Ret->setId($p_ParticleId);
		return $l_Ret;
	}

	public static function fromRaw(string $p_Raw):ParticleBuilder
	{
		$l_Ret = new ParticleBuilder();

		$l_Json = json_decode($p_Raw, true);
		if (!is_null($l_Json))
		{
			if (array_key_exists(ParticleBuilder::KEY_NAME, $l_Json))
			{
				$l_ParticleId = self::getParticleId($l_Json[ParticleBuilder::KEY_NAME]);

				$l_Ret->setId($l_ParticleId);
				switch ($l_ParticleId)
				{
					// SCALABLE
					case Particle::TYPE_HEART:
					case Particle::TYPE_CRITICAL:
					case Particle::TYPE_INK:
					case Particle::TYPE_REDSTONE:
						$l_Ret->setData(ArrayUtils::getKeyOrDefault($l_Json, ParticleBuilder::KEY_SCALE, 1));
						break;

					// COLOR
					case Particle::TYPE_FALLING_DUST:
					case Particle::TYPE_DUST:
						$l_RgbColor = ColorUtils::hexToRgb(ArrayUtils::getKeyOrDefault($l_Json, ParticleBuilder::KEY_COLOR, "#FFFFFF"));
						$l_Ret->setColor($l_RgbColor["r"], $l_RgbColor["g"], $l_RgbColor["b"]);
						break;

					// BLOCK_ID & ITEM_ID
					case Particle::TYPE_TERRAIN:
					case Particle::TYPE_ITEM_BREAK:
						$l_Ret->setData(ItemUtils::getItemIdFromName(ArrayUtils::getKeyOrDefault($l_Json, ParticleBuilder::KEY_DATA, "STONE")));
						break;

					// GENERIC
					case Particle::TYPE_BUBBLE:
					case Particle::TYPE_SMOKE:
					case Particle::TYPE_EXPLODE:
					case Particle::TYPE_EVAPORATION:
					case Particle::TYPE_FLAME:
					case Particle::TYPE_LAVA:
					case Particle::TYPE_LARGE_SMOKE:
					case Particle::TYPE_RISING_RED_DUST:
					case Particle::TYPE_SNOWBALL_POOF:
					case Particle::TYPE_HUGE_EXPLODE:
					case Particle::TYPE_HUGE_EXPLODE_SEED:
					case Particle::TYPE_MOB_FLAME:
					case Particle::TYPE_SUSPENDED_TOWN:
					case Particle::TYPE_PORTAL:
					case Particle::TYPE_SPLASH:
					case Particle::TYPE_WATER_SPLASH:
					case Particle::TYPE_WATER_WAKE:
					case Particle::TYPE_DRIP_WATER:
					case Particle::TYPE_DRIP_LAVA:
					case Particle::TYPE_MOB_SPELL:
					case Particle::TYPE_MOB_SPELL_AMBIENT:
					case Particle::TYPE_MOB_SPELL_INSTANTANEOUS:
					case Particle::TYPE_SLIME:
					case Particle::TYPE_RAIN_SPLASH:
					case Particle::TYPE_ENCHANTMENT_TABLE:
					case Particle::TYPE_TRACKING_EMITTER:
					case Particle::TYPE_NOTE:
					case Particle::TYPE_WITCH_SPELL:
					case Particle::TYPE_CARROT:
					case Particle::TYPE_BLOCK_FORCE_FIELD:
					case Particle::TYPE_VILLAGER_ANGRY:
					case Particle::TYPE_VILLAGER_HAPPY:
					case Particle::TYPE_END_ROD:
					case Particle::TYPE_DRAGONS_BREATH:
						$l_Ret->setData(ArrayUtils::getKeyOrDefault($l_Json, ParticleBuilder::KEY_DATA, 0));
						break;
				}
			}
		} else
		{
			FatUtils::getInstance()->getLogger()->error("Incorrect JSON parse for $p_Raw");
		}

		return $l_Ret;
	}

	public function setId(int $p_ParticleId):ParticleBuilder
	{
		$this->m_Id = $p_ParticleId;
		return $this;
	}

	public function setData($p_Data):ParticleBuilder
	{
		$this->m_Data = $p_Data;
		return $this;
	}

	public function setColor($r = 255, $g = 255, $b = 255, $a = 255):ParticleBuilder
	{
		$this->m_Data = (($a & 0xff) << 24) | (($r & 0xff) << 16) | (($g & 0xff) << 8) | ($b & 0xff);
		return $this;
	}

	private function __construct(){}

	private static function getParticleId(string $p_Raw)
	{
		if (is_null(self::$m_ParticleNames))
		{
			$class = new ReflectionClass(Particle::class);
			self::$m_ParticleNames = $class->getConstants();
		}

		if (array_key_exists($p_Raw, self::$m_ParticleNames))
			return self::$m_ParticleNames[$p_Raw];

		FatUtils::getInstance()->getLogger()->error("Raw Material does not exist: " . $p_Raw);
		return Particle::TYPE_REDSTONE;
	}

	private function generateParticle()
	{
		$this->m_Particle = new FatcraftGenericParticle($this->m_Id, $this->m_Data);
	}

	public function playForPlayer(Position $p_Position, Player $p_Player)
	{
		if (is_null($this->m_Particle))
			$this->generateParticle();

		if ($this->m_Particle instanceof FatcraftGenericParticle)
		{
			$this->m_Particle->setPosition($p_Position);
			$p_Player->getLevel()->addParticle(clone $this->m_Particle, [$p_Player]);
		}
	}

	public function playForPlayers(Position $p_Position, array $p_Players)
	{
		if (is_null($this->m_Particle))
			$this->generateParticle();

		if ($this->m_Particle instanceof FatcraftGenericParticle)
		{
			$this->m_Particle->setPosition($p_Position);
			$p_Position->getLevel()->addParticle(clone $this->m_Particle, $p_Players);
		}
	}

	public function play(Position $p_Position)
	{
		if (is_null($this->m_Particle))
			$this->generateParticle();

		if ($this->m_Particle instanceof FatcraftGenericParticle)
		{
			$this->m_Particle->setPosition($p_Position);
			$p_Position->getLevel()->addParticle(clone $this->m_Particle);
		}
	}

//	public function playForPlayersForAll(Position $p_Position, float $p_Radius)
//	{
//		if (!($this->m_Particle instanceof FatcraftGenericParticle))
//			$this->generateParticle();
//
//		if ($this->m_Particle instanceof FatcraftGenericParticle)
//		{
//			$this->m_Particle->setPosition($p_Position);
//			$l_NearbyEntities = $p_Position->getLevel()->getNearbyEntities(WorldUtils::getRadiusBB($p_Position, $p_Radius));
//			foreach ($l_NearbyEntities as $l_Entity)
//			{
//				if ($l_Entity instanceof Player)
//					$l_Entity->getLevel()->addParticle($this->m_Particle);
//			}
//		}
//	}
}