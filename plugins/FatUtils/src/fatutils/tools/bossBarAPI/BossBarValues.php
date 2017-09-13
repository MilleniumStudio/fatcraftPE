<?php

namespace fatutils\tools\bossBarAPI;

use pocketmine\entity\Attribute;

class BossBarValues extends Attribute{
	public $min, $max, $value, $name;

	public function __construct($min, $max, $value, $name){
		$this->min = $min;
		$this->max = $max;
		$this->value = $value;
		$this->name = $name;
	}

	public function getMinValue(): float{
		return $this->min;
	}

	public function getMaxValue(): float{
		return $this->max;
	}

	public function getValue(): float{
		return $this->value;
	}

	public function getName(): string{
		return $this->name;
	}

	public function getDefaultValue(): float{
		return $this->min;
	}
}