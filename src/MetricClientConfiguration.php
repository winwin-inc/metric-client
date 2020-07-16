<?php


namespace winwin\metric\client;

use DI\Annotation\Inject;
use kuiper\annotations\AnnotationReaderInterface;
use kuiper\di\annotation\Bean;
use kuiper\di\annotation\ConditionalOnProperty;
use kuiper\di\annotation\Configuration;
use wenbinye\tars\rpc\TarsClientFactoryInterface;
use winwin\metric\client\integration\MetricServant;

/**
 * @Configuration()
 * @ConditionalOnProperty("application.metric.server-name")
 */
class MetricClientConfiguration
{
    /**
     * @Bean()
     * @Inject({"serverName": "application.metric.server-name"})
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public function metricServant(TarsClientFactoryInterface $tarsClientFactory, string $serverName): MetricServant
    {
        return $tarsClientFactory->create(MetricServant::class, $serverName.'.MetricObj');
    }

    /**
     * @Bean()
     */
    public function metricService(MetricServant $metricServant, ScopeParser $scopeParser, AnnotationReaderInterface $annotationReader): MetricServiceInterface
    {
        return new MetricService($metricServant, $scopeParser, $annotationReader);
    }

    /**
     * @Bean()
     * @Inject({"namespace": "application.metric.namespace"})
     */
    public function scopeParser(ScopeRegistry $registry, ?string $namespace): ScopeParser
    {
        return new ScopeParser($registry, '_', $namespace);
    }
}
