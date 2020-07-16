<?php


namespace winwin\metric\client;

use kuiper\helper\Text;

class ScopeParser
{
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var string
     */
    private $delimiter;
    /**
     * @var ScopeRegistry
     */
    private $scopeRegistry;

    /**
     * ScopeParser constructor.
     * @param string $namespace
     * @param string $delimiter
     * @param ScopeRegistry $scopeRegistry
     */
    public function __construct(ScopeRegistry $scopeRegistry, string $delimiter = "_", ?string $namespace = null)
    {
        $this->namespace = $namespace;
        $this->delimiter = $delimiter;
        $this->scopeRegistry = $scopeRegistry;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function parse(string $scopeId): Scope
    {
        $scopes = $scopeId;
        if ($this->namespace) {
            if (!Text::startsWith($scopeId, $this->namespace)) {
                throw new \InvalidArgumentException("$scopeId should begin with namespace {$this->namespace}");
            }
            $scopes = substr($scopeId, strlen($this->namespace));
        }
        return new Scope(
            array_map([$this->scopeRegistry, 'create'], explode($this->delimiter, $scopes)),
            $scopeId
        );
    }

    /**
     * @param ScopeType[] $scopeTypes
     * @return Scope
     */
    public function create(array $scopeTypes): Scope
    {
        return new Scope($scopeTypes, $this->namespace . implode($this->delimiter, array_map(function (ScopeType $scopeType) {
            return $this->scopeRegistry->getAbbrev(get_class($scopeType)) . $scopeType->getId();
        }, $scopeTypes)));
    }
}
