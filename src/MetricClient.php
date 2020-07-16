<?php


namespace winwin\metric\client;

use Carbon\Carbon;
use DateTime;
use InvalidArgumentException;
use kuiper\annotations\AnnotationReaderInterface;
use kuiper\helper\Arrays;
use ReflectionClass;
use winwin\metric\client\annotation\EnumTag;
use winwin\metric\client\annotation\Metric as MetricAnnotation;
use winwin\metric\client\integration\MetricCriteria;
use winwin\metric\client\integration\MetricServant;

class MetricClient implements MetricClientInterface
{
    /**
     * @var MetricServiceInterface
     */
    private $metricFactory;

    /**
     * @var MetricServant
     */
    private $metricServant;

    /**
     * @var ReflectionClass
     */
    private $metricModelClass;

    /**
     * @var MetricFieldInterface[]
     */
    private $fields;

    /**
     * @var array
     */
    private $placeholderValues;

    /**
     * @var array
     */
    private $fieldMap;

    private static $METRIC_FIELDS = [];

    public function __construct(
        MetricServiceInterface $metricFactory,
        MetricServant $metricServant,
        AnnotationReaderInterface $annotationReader,
        ReflectionClass $metricModelClass,
        array $placeholderValues = [],
        array $selectFields = [],
        array $ignoreFields = []
    ) {
        $this->metricServant = $metricServant;
        if (!$metricModelClass->isSubclassOf(AbstractMetric::class)) {
            throw new InvalidArgumentException($metricModelClass->getName() . " should be subclass of " . AbstractMetric::class);
        }
        $this->metricModelClass = $metricModelClass;
        $this->fields = self::getMetricFields($metricModelClass, $annotationReader);
        $this->buildFieldMap($selectFields, $ignoreFields);
        $this->placeholderValues = $placeholderValues;
        $this->metricFactory = $metricFactory;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function withPlaceholder(array $placeholderValues): MetricClientInterface
    {
        $new = clone $this;
        $new->placeholderValues = $placeholderValues;
        return $new;
    }

    public function ignoreFields(array $fields): MetricClientInterface
    {
        $new = clone $this;
        $new->buildFieldMap(null, $fields);
        return $new;
    }

    public function selectFields(array $fields): MetricClientInterface
    {
        $new = clone $this;
        $new->buildFieldMap($fields, null);
        return $new;
    }

    /**
     * @inheritDoc
     */
    public function query($scope, DateTime $date): ?AbstractMetric
    {
        return $this->multiQuery([$scope], $date)[(string) $scope] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function multiQuery(array $scopes, DateTime $date): array
    {
        $modelData = $this->doQuery($scopes, $date, $date, $this->placeholderValues);
        $metricModels = [];
        foreach ($modelData as $scopeId => $scopeData) {
            foreach ($scopeData as $dateStr => $dailyValue) {
                $metricModels[$scopeId] = $this->createModel($scopeId, $dateStr, $dailyValue, $this->placeholderValues);
            }
        }
        return $metricModels;
    }

    /**
     * @inheritDoc
     */
    public function rangeQuery($scope, DateTime $startDate, DateTime $endDate): array
    {
        return $this->multiRangeQuery([$scope], $startDate, $endDate)[(string) $scope] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function multiRangeQuery(array $scopes, DateTime $startDate, DateTime $endDate): array
    {
        $modelData = $this->doQuery($scopes, $startDate, $endDate, $this->placeholderValues);
        $metricModels = [];
        foreach ($modelData as $scopeId => $scopeData) {
            foreach ($scopeData as $dateStr => $dailyValue) {
                $metricModels[$scopeId][$dateStr] = $this->createModel($scopeId, $dateStr, $dailyValue, $this->placeholderValues);
            }
        }
        return $metricModels;
    }

    /**
     * @inheritDoc
     */
    public function save(array $metrics): void
    {
        $seriesList = $this->createSeriesList($metrics, $this->placeholderValues);
        if (!empty($seriesList)) {
            $this->metricServant->save($seriesList);
        }
    }

    /**
     * @inheritDoc
     */
    public function incr(array $metrics): void
    {
        $seriesList = $this->createSeriesList($metrics, $this->placeholderValues);
        if (!empty($seriesList)) {
            $this->metricServant->incr($seriesList);
        }
    }

    private function buildFieldMap(?array $selectFields, ?array $ignoreFields): void
    {
        $map = [];
        foreach ($this->fields as $field) {
            $map[$field->getPropertyName()] = true;
        }
        if (!empty($selectFields)) {
            foreach ($map as $name) {
                $map[$name] = in_array($name, $selectFields, true);
            }
        }
        if (!empty($ignoreFields)) {
            foreach ($ignoreFields as $name) {
                if (isset($map[$name])) {
                    $map[$name] = false;
                }
            }
        }
        $this->fieldMap = $map;
    }

    private static function getMetricFields(ReflectionClass $metricModelClass, AnnotationReaderInterface $annotationReader): array
    {
        if (!isset(self::$METRIC_FIELDS[$metricModelClass->getName()])) {
            $fields = [];
            foreach ($metricModelClass->getProperties() as $property) {
                if ($property->isStatic()) {
                    continue;
                }
                /** @var MetricAnnotation $metricAnnotation */
                $metricAnnotation = $annotationReader->getPropertyAnnotation($property, MetricAnnotation::class);
                if (!$metricAnnotation) {
                    continue;
                }

                $enumTag = $annotationReader->getPropertyAnnotation($property, EnumTag::class);
                if ($enumTag) {
                    $fields[] = new EnumTaggedMetricField($property, $metricAnnotation->name, $metricAnnotation->tags ?? [], $enumTag);
                } else {
                    $fields[] = new MetricField($property, $metricAnnotation->name, $metricAnnotation->tags ?? []);
                }
            }
            self::$METRIC_FIELDS[$metricModelClass->getName()] = $fields;
        }
        return self::$METRIC_FIELDS[$metricModelClass->getName()];
    }

    private function createModel(string $scopeId, string $dateStr, array $dailyValue, array $placeholderValues)
    {
        $metricModel = $this->metricModelClass->newInstance($this->metricFactory->createScope($scopeId), Carbon::parse($dateStr));
        foreach ($this->fields as $field) {
            if ($this->fieldMap[$field->getPropertyName()]) {
                $field->setValue($metricModel, $dailyValue, $placeholderValues);
            }
        }
        return $metricModel;
    }

    /**
     * @param array $scopes
     * @param array $placeholderValues
     * @return \winwin\metric\client\integration\Metric[]
     */
    private function createMetrics(array $scopes, array $placeholderValues): array
    {
        if (empty($scopes)) {
            return [];
        }
        $metrics = [];
        foreach ($scopes as $scope) {
            foreach ($this->fields as $field) {
                if ($this->fieldMap[$field->getPropertyName()]) {
                    $metrics[] = $field->createMetrics((string) $scope, $placeholderValues);
                }
            }
        }
        return Arrays::flatten($metrics);
    }

    private function checkPlaceholder(array $placeholderValues): void
    {
        foreach ($this->fields as $field) {
            $placeholderTags = $field->getPlaceholderTags();
            if (empty($placeholderTags)) {
                continue;
            }
            $contains = array_intersect(array_keys($placeholderValues), $placeholderTags);

            if (count($contains) !== count($placeholderTags)) {
                throw new InvalidArgumentException(sprintf(
                    "%s placeholder value not given, require %s, given %s",
                    $field->getPropertyName(),
                    json_encode($placeholderTags),
                    json_encode(array_keys($placeholderValues))
                ));
            }
        }
    }

    protected function doQuery(array $scopes, DateTime $startDate, DateTime $endDate, array $placeholderValues): array
    {
        $this->checkPlaceholder($placeholderValues);
        $criteria = new MetricCriteria();
        $criteria->metrics = $this->createMetrics($scopes, $placeholderValues);
        $criteria->startDate = $startDate->format("Y-m-d");
        $criteria->endDate = $endDate->format("Y-m-d");
        if (empty($criteria->metrics)) {
            return [];
        }
        $seriesList = $this->metricServant->query($criteria);
        $modelData = [];
        foreach ($seriesList as $series) {
            /** @var \winwin\metric\client\integration\MetricSeries $series */
            foreach ($series->values as $date => $value) {
                $metric = $series->metric;
                $modelData[$metric->scopeId][$date][MetricHelper::toString($metric)] = $value;
            }
        }
        return $modelData;
    }

    protected function createSeriesList(array $metricModels, array $placeholderValues = []): array
    {
        if (empty($metricModels)) {
            return [];
        }
        foreach ($metricModels as $metric) {
            if (!($this->metricModelClass->isInstance($metric))) {
                throw new \InvalidArgumentException("Expected instance of " . $this->metricModelClass->getName()
                    . ', got ' . get_class($metric));
            }
        }
        $seriesList = [];
        foreach ($this->fields as $field) {
            if ($this->fieldMap[$field->getPropertyName()]) {
                $seriesList[] = $field->createSeriesList($metricModels, $placeholderValues);
            }
        }
        return Arrays::flatten($seriesList);
    }
}
