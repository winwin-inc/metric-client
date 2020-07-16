<?php


namespace winwin\metric\client;

use kuiper\helper\Text;

class ScopeRegistry
{
    /**
     * @var string[]
     */
    private $scopeTypes = [];

    /**
     * @var string
     */
    private $regex;

    public function register(string $abbrev, string $scopeClass): void
    {
        if (!preg_match('/^[a-zA-Z]$/', $abbrev)) {
            throw new \InvalidArgumentException("Invalid scope prefix $abbrev, it should be matching letters");
        }
        if (isset($this->scopeTypes[$abbrev])
            && $this->scopeTypes[$abbrev] !== $scopeClass) {
            throw new \InvalidArgumentException("$abbrev was already registered for " . $this->scopeTypes[$abbrev]);
        }
        foreach ($this->scopeTypes as $prefix => $type) {
            if (Text::startsWith($prefix, $abbrev) || Text::startsWith($abbrev, $prefix)) {
                throw new \InvalidArgumentException("$abbrev conflict with $prefix which was registered for $type");
            }
            if ($type === $scopeClass) {
                throw new \InvalidArgumentException("$type was already registered as $prefix");
            }
        }
        $this->scopeTypes[$abbrev] = $scopeClass;
        $this->regex = '#^(' . implode('|', array_keys($this->scopeTypes)) . ')#';
    }

    public function create(string $scope): ScopeType
    {
        if (!preg_match($this->regex, $scope, $matches)) {
            throw new \InvalidArgumentException("$scope is not a scope, scope should start with "
                . implode("|", array_keys($this->scopeTypes)));
        }
        $scopeType = $this->scopeTypes[$matches[1]];
        return new $scopeType(substr($scope, strlen($matches[1])));
    }

    public function getAbbrev(string $scopeClass): string
    {
        $key = array_search($scopeClass, $this->scopeTypes, true);
        if ($key === false) {
            throw new \InvalidArgumentException("scope $scopeClass was not registered");
        }
        return $key;
    }
}
