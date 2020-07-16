<?php


namespace winwin\metric\client;

use winwin\metric\client\integration\MetricSeries;

interface MetricFieldInterface
{
    public function getPropertyName(): string;

    /**
     * Assigns metricModel value
     *
     * @param AbstractMetric $metricModel
     * @param double[] $metricValues
     * @param array $placeholderValues
     */
    public function setValue(AbstractMetric $metricModel, array $metricValues, array $placeholderValues): void;

    /**
     * Creates metrics
     * @param string $scopeId
     * @param array $placeholderValues
     * @return \winwin\metric\client\integration\Metric[]
     */
    public function createMetrics(string $scopeId, array $placeholderValues): array ;

    /**
     * @param AbstractMetric[] $metricModels
     * @param array $placeholderValues
     * @return MetricSeries[]
     */
    public function createSeriesList(array $metricModels, array $placeholderValues): array ;

    /**
     * Gets placeholder tag names
     * @return string[]
     */
    public function getPlaceholderTags(): array ;
}
