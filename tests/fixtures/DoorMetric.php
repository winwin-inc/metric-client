<?php


namespace winwin\metric\client\fixtures;

use winwin\metric\client\AbstractMetric;
use winwin\metric\client\annotation\Metric;

class DoorMetric extends AbstractMetric
{
    /**
     * @Metric(name="count")
     * @var int
     */
    private $doorCount;

    /**
     * @return int
     */
    public function getDoorCount(): int
    {
        return $this->doorCount;
    }

    /**
     * @param int $doorCount
     */
    public function setDoorCount(int $doorCount): void
    {
        $this->doorCount = $doorCount;
    }
}
