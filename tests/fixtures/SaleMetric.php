<?php


namespace winwin\metric\client\fixtures;

use winwin\metric\client\AbstractMetric;
use winwin\metric\client\annotation\EnumTag;
use winwin\metric\client\annotation\Metric;

class SaleMetric extends AbstractMetric
{
    /**
     * @EnumTag(name="hour", type=Hour::class)
     * @Metric(name="sale_unit", tags={"hour":null})
     * @var double[]
     */
    private $hourlySaleUnits;

    /**
     * @Metric(name="sale_unit")
     * @var double
     */
    private $saleUnits;

    /**
     * @return double[]
     */
    public function getHourlySaleUnits(): array
    {
        return $this->hourlySaleUnits;
    }

    /**
     * @param double[] $hourlySaleUnits
     */
    public function setHourlySaleUnits(array $hourlySaleUnits): void
    {
        $this->hourlySaleUnits = $hourlySaleUnits;
    }

    /**
     * @return float
     */
    public function getSaleUnits(): float
    {
        return $this->saleUnits;
    }

    /**
     * @param float $saleUnits
     */
    public function setSaleUnits(float $saleUnits): void
    {
        $this->saleUnits = $saleUnits;
    }
}
