<?php


namespace winwin\metric\client;

use DateTime;

interface MetricClientInterface
{
    /**
     * Gets all fields
     *
     * @return MetricFieldInterface[]
     */
    public function getFields(): array;

    /**
     * @param array $placeholderValues
     * @return MetricClientInterface
     */
    public function withPlaceholder(array $placeholderValues): MetricClientInterface;

    /**
     * Ignore fields
     *
     * @param string[] $fields
     * @return MetricClientInterface
     */
    public function ignoreFields(array $fields): MetricClientInterface;

    /**
     * Choose fields
     *
     * @param string[] $fields
     * @return MetricClientInterface
     */
    public function selectFields(array $fields): MetricClientInterface;

    /**
     * Query metric by scopeId
     *
     * @param string|Scope $scope
     * @param DateTime $date
     * @return AbstractMetric|null
     */
    public function query($scope, DateTime $date): ?AbstractMetric;

    /**
     * Query metric by multiple scopeId
     * @param string[]|Scope[] $scopes
     * @param DateTime $date
     * @return AbstractMetric[] array associated by scopeId
     */
    public function multiQuery(array $scopes, DateTime $date): array;

    /**
     * Query metric by date range
     * @param string|Scope $scope
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return AbstractMetric[] array associated by date
     */
    public function rangeQuery($scope, DateTime $startDate, DateTime $endDate): array;

    /**
     * Query metric by multiple scopeId and date range
     * @param string[]|Scope[] $scopes
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return AbstractMetric[][] array associated by scopeId and item associated by date
     */
    public function multiRangeQuery(array $scopes, DateTime $startDate, DateTime $endDate): array;

    /**
     * Save metric values
     * @param AbstractMetric[] $metrics
     */
    public function save(array $metrics): void;

    /**
     * Increase metric values
     * @param AbstractMetric[] $metrics
     */
    public function incr(array $metrics): void;
}
