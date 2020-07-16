<?php

declare(strict_types=1);

use winwin\metric\client\integration\Metric;
use winwin\metric\client\integration\MetricCriteria;
use winwin\metric\client\integration\MetricSeries;

function metric($scope, string $name, array $tags = []): Metric
{
    $metric = new Metric();
    $metric->scopeId = (string) $scope;
    $metric->name = $name;
    $metric->tags = $tags;

    return $metric;
}

function series(Metric $metric, array $values): MetricSeries
{
    $series = new MetricSeries();
    $series->metric = $metric;
    $series->values = $values;

    return $series;
}

function criteria(array $metrics, string $startDate, ?string $endDate = null): MetricCriteria
{
    $criteria = new MetricCriteria();
    $criteria->metrics = $metrics;
    $criteria->startDate = $startDate;
    $criteria->endDate = $endDate;

    return $criteria;
}
