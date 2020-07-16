<?php


namespace winwin\metric\client;

use DateTime;
use winwin\metric\client\integration\MetricAggCriteria;
use winwin\metric\client\integration\MetricSeries;
use winwin\metric\client\integration\MetricServant;
use winwin\metric\client\integration\TimeAggregation;

class AggregateQueryBuilder
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
     * @var string
     */
    private $scopePattern;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $tags;

    /**
     * @var DateTime
     */
    private $startDate;

    /**
     * @var DateTime|null
     */
    private $endDate;

    /**
     * @var TimeAggregation|null
     */
    private $timeAggregation;

    /**
     * AggregateQueryBuilder constructor.
     * @param MetricServiceInterface $metricFactory
     * @param MetricServant $metricServant
     */
    public function __construct(MetricServiceInterface $metricFactory, MetricServant $metricServant)
    {
        $this->metricFactory = $metricFactory;
        $this->metricServant = $metricServant;
        $this->tags = [];
        $this->timeAggregation = TimeAggregation::fromValue(TimeAggregation::NONE);
    }

    /**
     * @return string
     */
    public function getScopePattern(): string
    {
        return $this->scopePattern;
    }

    /**
     * @param string $scopePattern
     * @return AggregateQueryBuilder
     */
    public function setScopePattern(string $scopePattern): AggregateQueryBuilder
    {
        $this->scopePattern = $scopePattern;
        return $this;
    }

    public function scopeMatch(...$scopes): AggregateQueryBuilder
    {
        $this->scopePattern = '^' . $this->metricFactory->createScope($scopes);
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AggregateQueryBuilder
     */
    public function setName(string $name): AggregateQueryBuilder
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return AggregateQueryBuilder
     */
    public function setTags(array $tags): AggregateQueryBuilder
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     * @return AggregateQueryBuilder
     */
    public function setStartDate(string $startDate): AggregateQueryBuilder
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     * @return AggregateQueryBuilder
     */
    public function setEndDate(string $endDate): AggregateQueryBuilder
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return TimeAggregation
     */
    public function getTimeAggregation(): ?TimeAggregation
    {
        return $this->timeAggregation;
    }

    /**
     * @param TimeAggregation $timeAggregation
     * @return AggregateQueryBuilder
     */
    public function setTimeAggregation($timeAggregation): AggregateQueryBuilder
    {
        $this->timeAggregation = $timeAggregation instanceof TimeAggregation
            ? $timeAggregation
            : TimeAggregation::fromValue($timeAggregation);
        return $this;
    }

    public function query(): MetricSeries
    {
        if (!isset($this->name, $this->startDate, $this->scopePattern)) {
            throw new \InvalidArgumentException("");
        }
        $criteria = new MetricAggCriteria();
        $criteria->name = $this->name;
        $criteria->startDate = $this->startDate->format('Y-m-d');
        $criteria->endDate = $this->endDate ? $this->endDate->format('Y-m-d')
            : $criteria->startDate;
        $criteria->scopePattern = $this->scopePattern;
        $criteria->tags = $this->tags;
        return $this->metricServant->aggregateQuery($criteria);
    }
}
