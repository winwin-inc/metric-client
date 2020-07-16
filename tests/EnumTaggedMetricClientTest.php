<?php

namespace winwin\metric\client;

use Carbon\Carbon;
use winwin\metric\client\fixtures\DoorScope;
use winwin\metric\client\fixtures\Hour;
use winwin\metric\client\fixtures\SaleMetric;
use winwin\metric\client\integration\MetricCriteria;

class EnumTaggedMetricClientTest extends AbstractMetricClientTestCase
{
    public function testQuery()
    {
        $client = $this->createClient(SaleMetric::class);
        $scope = $this->metricService->createScope(new DoorScope(1));
        $this->metricServant->shouldReceive('query')
            ->with(\Mockery::capture($criteria))
            ->andReturn([
                series(metric($scope, 'sale_unit'), ['2020-06-01' => 6]),
                series(metric($scope, 'sale_unit', ['hour' => '0']), ['2020-06-01' => 1]),
                series(metric($scope, 'sale_unit', ['hour' => '11']), ['2020-06-01' => 2]),
                series(metric($scope, 'sale_unit', ['hour' => '20']), ['2020-06-01' => 3])
            ]);
        /** @var SaleMetric $result */
        $result = $client->query($scope, Carbon::parse("2020-06-01"));
        // print_r($result);
        $this->assertInstanceOf(SaleMetric::class, $result);
        $this->assertEquals(6, $result->getSaleUnits());
        /** @var MetricCriteria $criteria */
        // print_r($criteria);
        $this->assertCount(25, $criteria->metrics);
        // $this->assertEquals(3, $result->getDoorCount());
    }

    public function testSave()
    {
        $client = $this->createClient(SaleMetric::class);
        $scope = $this->metricService->createScope(new DoorScope(1));
        $this->metricServant->shouldReceive('save')
            ->with(\Mockery::capture($seriesList));
        /** @var SaleMetric $result */
        $metric = new SaleMetric($scope, Carbon::parse("2020-06-01"));
        $metric->setSaleUnits(6);
        $metric->setHourlySaleUnits([
            Hour::HOUR_02 => 1,
            Hour::HOUR_13 => 2,
            Hour::HOUR_23 => 3
        ]);
        $client->save([$metric]);
        // print_r($seriesList);
        $this->assertCount(4, $seriesList);
    }
}
