<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 17/10/2017
 * Time: 10:23
 */

namespace fatutils\tools\animations;


use fatutils\tools\Timer;

abstract class Animation
{
	public abstract function play();
	public abstract function pause();
	public abstract function stop();
}