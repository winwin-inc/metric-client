<?php

namespace winwin\metric\client;

use Carbon\Carbon;
use winwin\metric\client\fixtures\DoorMetric;
use winwin\metric\client\fixtures\DoorScope;
use winwin\metric\client\integration\MetricCriteria;

class MetricClientTestCase extends AbstractMetricClientTestCase
{
    public function testQuery()
    {
        $client = $this->createClient(DoorMetric::class);
        $scope = $this->metricService->createScope(new DoorScope(1));
        $this->metricServant->shouldReceive('query')
            ->with(\Mockery::capture($criteria))
            ->andReturn([
                series(metric($scope, 'count'), ['2020-06-01' => 3])
            ]);
        $result = $client->query($scope, Carbon::parse("2020-06-01"));
        // print_r($result);
        $this->assertInstanceOf(DoorMetric::class, $result);
        $this->assertEquals(3, $result->getDoorCount());
        // print_r($criteria);
        /** @var MetricCriteria $criteria */
        $this->assertCount(1, $criteria->metrics);
    }

    public function testSave()
    {
        $client = $this->createClient(DoorMetric::class);
        $scope = $this->metricService->createScope(new DoorScope(1));
        $this->metricServant->shouldReceive('save')
            ->with(\Mockery::capture($seriesList));
        $metric = new DoorMetric($scope, Carbon::parse("2020-06-01"));
        $metric->setDoorCount(3);
        $client->save([$metric]);
        // print_r($seriesList);
        $this->assertCount(1, $seriesList);
        $this->assertEquals(['2020-06-01' => 3], $seriesList[0]->values);
    }
}
