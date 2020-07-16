<?php


namespace winwin\metric\client;

use kuiper\annotations\AnnotationReaderInterface;
use winwin\metric\client\integration\MetricServant;

class MetricService implements MetricServiceInterface
{
    /**
     * @var MetricServant
     */
    private $metricServant;

    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * @var ScopeParser
     */
    private $scopeParser;

    /**
     * MetricFactory constructor.
     * @param MetricServant $metricServant
     * @param ScopeParser $scopeParser
     * @param AnnotationReaderInterface $annotationReader
     */
    public function __construct(MetricServant $metricServant, ScopeParser $scopeParser, AnnotationReaderInterface $annotationReader)
    {
        $this->metricServant = $metricServant;
        $this->annotationReader = $annotationReader;
        $this->scopeParser = $scopeParser;
    }

    /**
     * @inheritDoc
     */
    public function createClient(string $metricModelClass): MetricClientInterface
    {
        return new MetricClient($this, $this->metricServant, $this->annotationReader, new \ReflectionClass($metricModelClass));
    }

    /**
     * @inheritDoc
     */
    public function createScope(...$scopes): Scope
    {
        if (empty($scopes)) {
            throw new \InvalidArgumentException("scope is required");
        }
        if (is_string($scopes[0])) {
            return $this->scopeParser->parse($scopes[0]);
        }
        return $this->scopeParser->create($scopes);
    }

    /**
     * @inheritDoc
     */
    public function tagQuery(): TagQueryBuilder
    {
        return new TagQueryBuilder($this, $this->metricServant);
    }

    /**
     * @inheritDoc
     */
    public function aggregateQuery(): AggregateQueryBuilder
    {
        return new AggregateQueryBuilder($this, $this->metricServant);
    }
}
