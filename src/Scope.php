<?php


namespace winwin\metric\client;

class Scope implements \ArrayAccess
{
    /**
     * @var ScopeType[]
     */
    private $scopeTypes;

    /**
     * @var string
     */
    private $scopeId;

    public function __construct(array $scopeTypes, string $scopeId)
    {
        $this->scopeTypes = $scopeTypes;
        $this->scopeId = $scopeId;
    }

    /**
     * @return string
     */
    public function getScopeId(): string
    {
        return $this->scopeId;
    }

    public function __toString()
    {
        return $this->scopeId;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->scopeTypes[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->scopeTypes[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException("Cannot change scope");
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException("Cannot change scope");
    }
}
