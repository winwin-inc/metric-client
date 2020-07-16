<?php

namespace winwin\metric\client;

use kuiper\helper\Text;

abstract class AbstractMetric
{

    /**
     * @var Scope
     */
    private $scope;

    /**
     * @var \DateTime
     */
    private $date;

    public function __construct(Scope $scope, \DateTime $date)
    {
        $this->scope = $scope;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getScopeId(): string
    {
        return $this->scope->getScopeId();
    }

    /**
     * @return Scope
     */
    public function getScope(): Scope
    {
        return $this->scope;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getDateString(): string
    {
        return $this->date->format("Y-m-d");
    }
}
