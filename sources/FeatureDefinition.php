<?php

namespace Trompette\FeatureToggles;

class FeatureDefinition
{
    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string */
    private $strategy;

    public function __construct(string $name, string $description, string $strategy)
    {
        $this->name = $name;
        $this->description = $description;
        $this->strategy = $strategy;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }
}
