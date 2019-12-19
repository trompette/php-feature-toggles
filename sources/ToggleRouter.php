<?php

namespace Trompette\FeatureToggles;

use Assert\Assert;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class ToggleRouter
{
    /** @var FeatureRegistry */
    private $registry;

    /** @var TogglingStrategy[] */
    private $strategies;

    public function __construct(FeatureRegistry $registry, array $strategies = [])
    {
        Assert::thatAll($strategies)->isInstanceOf(TogglingStrategy::class);

        $this->registry = $registry;
        $this->strategies = $strategies;
    }

    public function hasFeature(string $target, string $feature): bool
    {
        if (!$this->registry->exists($feature)) {
            return false;
        }

        $expression = $this->registry->getDefinition($feature)->getStrategy();
        $values = array_map(
            function (TogglingStrategy $strategy) use ($target, $feature) {
                return $strategy->decideIfTargetHasFeature($target, $feature);
            },
            $this->strategies
        );

        try {
            return (bool) (new ExpressionLanguage())->evaluate($expression, $values);
        } catch (SyntaxError $e) {
            return false;
        }
    }

    public function getFeatureConfiguration(string $feature): array
    {
        return array_map(
            function (TogglingStrategy $strategy) use ($feature) {
                return $strategy->getConfiguration($feature);
            },
            $this->strategies
        );
    }

    public function configureFeature(string $feature, string $strategy, string $method, $parameters = []): void
    {
        Assert::that($this->strategies)->keyExists($strategy, "$strategy is an invalid strategy");
        Assert::that($method)->methodExists($this->strategies[$strategy], "$method() is absent from strategy");

        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        $this->strategies[$strategy]->$method(...array_merge($parameters, [$feature]));
    }
}
