<?php


namespace winwin\metric\client;

use DateTime;
use winwin\metric\client\integration\MetricSeries;
use winwin\metric\client\integration\MetricServant;
use winwin\metric\client\integration\MetricTagCriteria;

class TagQueryBuilder
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
     * @var Scope
     */
    private $scope;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $tagPatterns;

    /**
     * @var DateTime
     */
    private $startDate;

    /**
     * @var DateTime|null
     */
    private $endDate;

    /**
     * @var bool
     */
    private $matchAll = false;

    /**
     * TagQueryBuilder constructor.
     * @param MetricServiceInterface $metricFactory
     * @param MetricServant $metricServant
     */
    public function __construct(MetricServiceInterface $metricFactory, MetricServant $metricServant)
    {
        $this->metricServant = $metricServant;
        $this->metricFactory = $metricFactory;
    }

    /**
     * @return string
     */
    public function getScopeId(): ?string
    {
        return $this->scope ? (string) $this->scope : null;
    }

    /**
     * @return Scope
     */
    public function getScope(): ?Scope
    {
        return $this->scope;
    }

    public function setScope(...$scopes): TagQueryBuilder
    {
        $this->scope = $this->metricFactory->createScope(...$scopes);
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return TagQueryBuilder
     */
    public function setName(string $name): TagQueryBuilder
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getTagPatterns(): ?array
    {
        return $this->tagPatterns;
    }

    public function matchTag(string $tag, string $pattern = null): TagQueryBuilder
    {
        $this->tagPatterns[$tag] = $pattern ?: '.*';
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     * @return TagQueryBuilder
     */
    public function setStartDate(DateTime $startDate): TagQueryBuilder
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     * @return TagQueryBuilder
     */
    public function setEndDate(DateTime $endDate): TagQueryBuilder
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMatchAll(): bool
    {
        return $this->matchAll;
    }

    /**
     * @param bool $matchAll
     * @return TagQueryBuilder
     */
    public function setMatchAll(bool $matchAll): TagQueryBuilder
    {
        $this->matchAll = $matchAll;
        return $this;
    }

    /**
     * @return MetricSeries[]
     */
    public function query(): array
    {
        if (!isset($this->name, $this->startDate, $this->scopeId, $this->tagPatterns)) {
            throw new \InvalidArgumentException("");
        }
        $criteria = new MetricTagCriteria();
        $criteria->scopeId = $this->scopeId;
        $criteria->name = $this->name;
        $criteria->startDate = $this->startDate->format('Y-m-d');
        $criteria->endDate = $this->endDate ? $this->endDate->format('Y-m-d')
            : $criteria->startDate;
        $criteria->tagPatterns = $this->tagPatterns;
        $criteria->matchAll = $this->matchAll;
        return $this->metricServant->queryByTag($criteria);
    }
}
