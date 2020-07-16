<?php

namespace winwin\metric\client;

use Carbon\Carbon;
use kuiper\annotations\AnnotationReader;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use winwin\metric\client\fixtures\BranchScope;
use winwin\metric\client\fixtures\ClientScope;
use winwin\metric\client\fixtures\DoorMetric;
use winwin\metric\client\fixtures\DoorScope;
use winwin\metric\client\integration\MetricCriteria;
use winwin\metric\client\integration\MetricServant;

abstract class AbstractMetricClientTestCase extends TestCase
{
    /**
     * @var MockInterface
     */
    protected $metricServant;

    /**
     * @var MetricServiceInterface
     */
    protected $metricService;

    protected function createClient(string $class): MetricClientInterface
    {
        $this->metricServant = \Mockery::mock(MetricServant::class);
        $scopeRegistry = new ScopeRegistry();
        $scopeRegistry->register("d", DoorScope::class);
        $scopeRegistry->register("c", ClientScope::class);
        $scopeRegistry->register("b", BranchScope::class);
        $scopeParser = new ScopeParser($scopeRegistry);
        $this->metricService = new MetricService($this->metricServant, $scopeParser, AnnotationReader::getInstance());
        return $this->metricService->createClient($class);
    }
}
