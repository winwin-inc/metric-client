<?php


namespace winwin\metric\client;

use InvalidArgumentException;
use kuiper\helper\Arrays;
use ReflectionProperty;
use winwin\metric\client\annotation\EnumTag;

class EnumTaggedMetricField extends MetricField
{
    /**
     * @var array
     */
    private $enumValues;

    /**
     * @var EnumTag
     */
    private $enumTag;

    /**
     * MetricField constructor.
     * @param ReflectionProperty $property
     * @param string $name
     * @param array $tags
     */
    public function __construct(ReflectionProperty $property, string $name, array $tags, EnumTag $enumTag)
    {
        parent::__construct($property, $name, $tags);
        if (!array_key_exists($enumTag->name, $this->getTags())) {
            throw new InvalidArgumentException($property->getDeclaringClass()->getName() . '.' . $property->getName()
                . " does not contain tag with name " . $enumTag->name);
        }
        $this->enumTag = $enumTag;
        if ($enumTag->type) {
            $this->enumValues = call_user_func([$enumTag->type, 'values']);
        } else {
            $this->enumValues = $enumTag->values;
        }
    }

    /**
     * @inheritDoc
     */
    public function createMetrics(string $scopeId, array $placeholderKeys): array
    {
        $metrics = [];
        $tags = $this->replaceTags($placeholderKeys);
        foreach ($this->enumValues as $enumValue) {
            $tags[$this->enumTag->name] = $enumValue;
            $metrics[] = MetricHelper::metric($scopeId, $this->getName(), $tags);
        }
        return $metrics;
    }

    /**
     * @inheritDoc
     */
    public function setValue(AbstractMetric $model, array $metricValues, array $placeholderValues): void
    {
        $values = [];
        $tags = $this->replaceTags($placeholderValues);
        foreach ($this->enumValues as $enumValue) {
            $tags[$this->enumTag->name] = $enumValue;
            $key = MetricHelper::toMetricString($model->getScopeId(), $this->getName(), $tags);
            if (isset($metricValues[$key])) {
                $values[$enumValue] = $metricValues[$key];
            }
        }
        if (!empty($values)) {
            call_user_func($this->getSetter(), $model, $values);
        }
    }

    public function createSeriesList(array $metricModels, array $placeholderValues): array
    {
        if (empty($metricModels)) {
            return [];
        }
        $tags = $this->replaceTags($placeholderValues);
        $metrics = [];
        $metricValues = [];
        foreach (Arrays::groupBy($metricModels, 'scopeId') as $scopeId => $scopeModels) {
            /** @var AbstractMetric[] $scopeModels */
            foreach ($scopeModels as $model) {
                $values = $this->getProperty()->getValue($model);
                foreach ($values as $key => $value) {
                    $tags[$this->enumTag->name] = $key;
                    $key = MetricHelper::toMetricString($scopeId, $this->getName(), $tags);
                    if (!isset($metrics[$key])) {
                        $metrics[$key] = MetricHelper::metric($scopeId, $this->getName(), $tags);
                    }
                    $metricValues[$key][$model->getDateString()] = (double) $value;
                }
            }
        }
        $seriesList = [];
        foreach ($metricValues as $key => $values) {
            $seriesList[] = MetricHelper::series($metrics[$key], $values);
        }
        return $seriesList;
    }
}
