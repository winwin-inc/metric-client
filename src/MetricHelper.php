<?php


namespace winwin\metric\client;

use winwin\metric\client\integration\Metric;
use winwin\metric\client\integration\MetricSeries;

class MetricHelper
{
    private static function serializeTags(?array $tags): string
    {
        if (empty($tags)) {
            return '';
        }
        ksort($tags);
        return http_build_query($tags);
    }

    public static function toMetricString(string $scopeId, string $name, ?array $tags): string
    {
        return sprintf('%s.%s[%s]', $scopeId, $name, self::serializeTags($tags));
    }

    public static function toString(Metric $metric): string
    {
        return self::toMetricString($metric->scopeId, $metric->name, $metric->tags);
    }

    public static function metric(string $scopeId, string $name, ?array $tags): Metric
    {
        $metric = new Metric();
        $metric->scopeId = $scopeId;
        $metric->name=  $name;
        $metric->tags = $tags;
        return $metric;
    }

    public static function series(Metric $metric, array $values): MetricSeries
    {
        $metricSeries = new MetricSeries();
        $metricSeries->metric = $metric;
        $metricSeries->values = $values;
        return $metricSeries;
    }
}
