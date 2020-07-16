<?php

namespace winwin\metric\client;

use PHPUnit\Framework\TestCase;
use winwin\metric\client\fixtures\BranchScope;
use winwin\metric\client\fixtures\ClientScope;
use winwin\metric\client\fixtures\DoorMetric;

class ScopeTest extends AbstractMetricClientTestCase
{
    public function testName()
    {
        $this->createClient(DoorMetric::class);
        $scope = $this->metricService->createScope(new ClientScope(1), new BranchScope(2));
        $this->assertEquals(1, $scope[0]->getId());
        $this->assertEquals(2, $scope[1]->getId());
        $this->assertEquals('c1_b2', (string) $scope);
    }
}
