<?php


namespace winwin\metric\client;

abstract class AbstractScopeType implements ScopeType
{
    /**
     * @var string
     */
    private $scopeId;

    /**
     * AbstractScopeType constructor.
     * @param string $scopeId
     */
    public function __construct($scopeId)
    {
        $this->scopeId = (string)$scopeId;
    }

    public function getId(): string
    {
        return $this->scopeId;
    }
}
