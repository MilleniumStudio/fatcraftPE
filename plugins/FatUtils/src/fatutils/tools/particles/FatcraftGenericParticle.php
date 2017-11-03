<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 27/10/2017
 * Time: 10:28
 */

namespace fatutils\tools\particles;


use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class FatcraftGenericParticle extends Particle
{
	protected $id;
	protected $data;

	public function __construct($id, $data = 0){
		parent::__construct();
		$this->id = $id & 0xFFF;
		$this->data = $data;
	}

	public function setPosition(Vector3 $p_Position)
	{
		$this->x = $p_Position->x;
		$this->y = $p_Position->y;
		$this->z = $p_Position->z;
	}

	public function encode(){
		$pk = new LevelEventPacket;
		$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | $this->id;
		$pk->position = $this;
		$pk->data = $this->data;

		return $pk;
	}
}