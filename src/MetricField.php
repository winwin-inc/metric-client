<?php


namespace winwin\metric\client;

use kuiper\helper\Arrays;

class MetricField implements MetricFieldInterface
{
    /**
     * @var \ReflectionProperty
     */
    private $property;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $tags;

    /**
     * @var callable
     */
    private $setter;

    /**
     * @var array
     */
    private $placeholderTags = [];

    /**
     * MetricField constructor.
     * @param \ReflectionProperty $property
     * @param string $name
     * @param array $tags
     */
    public function __construct(\ReflectionProperty $property, string $name, array $tags)
    {
        $property->setAccessible(true);
        $this->property = $property;
        $this->setter = $this->createSetter($property);
        $this->name = $name;
        $this->tags = $tags;
        if (!empty($tags)) {
            foreach ($tags as $key => $value) {
                if ($value === TagValue::PLACEHOLDER) {
                    $this->placeholderTags[] = $key;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setValue(AbstractMetric $model, array $metricValues, array $placeholderValues): void
    {
        $key = MetricHelper::toMetricString($model->getScopeId(), $this->name, $this->replaceTags($placeholderValues));
        if (isset($metricValues[$key])) {
            call_user_func($this->setter, $model, $metricValues[$key]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getPropertyName(): string
    {
        return $this->property->getName();
    }

    /**
     * @inheritDoc
     */
    public function createMetrics(string $scopeId, array $placeholderValues): array
    {
        return [MetricHelper::metric($scopeId, $this->name, $this->replaceTags($placeholderValues))];
    }

    /**
     * @inheritDoc
     */
    public function createSeriesList(array $metricModels, array $placeholderValues): array
    {
        if (empty($metricModels)) {
            return [];
        }
        $seriesList = [];
        /** @var AbstractMetric[] $scopeModels */
        foreach (Arrays::groupBy($metricModels, 'scopeId') as $scopeId => $scopeModels) {
            $metric = MetricHelper::metric($scopeId, $this->name, $this->replaceTags($placeholderValues));
            $values = [];
            foreach ($scopeModels as $model) {
                $values[$model->getDateString()] = (double) $this->property->getValue($model);
            }
            $seriesList[] = MetricHelper::series($metric, $values);
        }
        return $seriesList;
    }

    /**
     * @inheritDoc
     */
    public function getPlaceholderTags(): array
    {
        return $this->placeholderTags;
    }

    /**
     * @return \ReflectionProperty
     */
    public function getProperty(): \ReflectionProperty
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    protected function getSetter(): callable
    {
        return $this->setter;
    }

    protected function replaceTags(array $placeholderValues): array
    {
        if (empty($this->placeholderTags)) {
            return $this->tags;
        }
        $tags = $this->tags;
        foreach ($placeholderValues as $key => $value) {
            if (in_array($key, $this->placeholderTags, true)) {
                $tags[$key] = $value;
            }
        }
        return $tags;
    }

    private function createSetter(\ReflectionProperty $property): callable
    {
        $setter = 'set' . $property->getName();
        if (method_exists($property->getDeclaringClass()->getName(), $setter)) {
            return static function ($model, $value) use ($setter) {
                return $model->$setter($value);
            };
        }

        return static function ($model, $value) use ($property) {
            $property->setValue($model, $value);
        };
    }
}
