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
	protected $vector3;

	public function __construct($id, $data = 0){
		parent::__construct();
		$this->id = $id & 0xFFF;
		$this->data = $data;
	}

	public function setPosition(Vector3 $p_Position)
	{
		$this->vector3 = $p_Position;
	}

	public function encode(){
		$pk = new LevelEventPacket;
		$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | $this->id;
		$pk->position = $this->vector3;
		$pk->data = $this->data;

		return $pk;
	}
}