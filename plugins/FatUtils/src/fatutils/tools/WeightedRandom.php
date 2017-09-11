<?php
/**
 * Created by IntelliJ IDEA.
 * User: Nyhven
 * Date: 11/09/2017
 * Time: 13:39
 */

namespace fatutils\tools;


class WeightedRandom
{
    private $totalWeight = 0;
    private $repartitionTable = [];

    /**
     * WeightedRandom constructor.
     * @param array $p_ArrayIndexWeight
     */
    public function __construct(array $p_ArrayIndexWeight)
    {
        for ($i = 0 ; $i < count($p_ArrayIndexWeight) ; $i++)
        {
            $this->totalWeight += $p_ArrayIndexWeight[$i];
            $this->repartitionTable[$i] = $this->totalWeight;
        }
    }

    public function getRandomIndex():int
    {
        $r = rand(0, $this->totalWeight);
        for ($i = 0, $l = count($this->repartitionTable); $i < $l; $i++)
        {
            if ($r <= $this->repartitionTable[$i])
                return $i;
        }

        echo $r;
        return -1;
    }
}