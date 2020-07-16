<?php


namespace winwin\metric\client;

interface MetricServiceInterface
{
    /**
     * @param string $metricModelClass
     * @return MetricClientInterface
     */
    public function createClient(string $metricModelClass): MetricClientInterface;

    /**
     * @param string[]|ScopeType[] $scopes
     * @return Scope
     */
    public function createScope(...$scopes): Scope;

    /**
     * @return TagQueryBuilder
     */
    public function tagQuery(): TagQueryBuilder;

    /**
     * @return AggregateQueryBuilder
     */
    public function aggregateQuery(): AggregateQueryBuilder;
}