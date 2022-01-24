<?php

namespace Trompette\FeatureToggles;

use Assert\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * @property LoggerInterface $logger
 */
class ToggleRouter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private FeatureRegistry $registry;

    /** @var TogglingStrategy[] */
    private array $strategies;

    public function __construct(FeatureRegistry $registry, array $strategies = [])
    {
        Assert::thatAll($strategies)->isInstanceOf(TogglingStrategy::class);

        $this->registry = $registry;
        $this->strategies = $strategies;

        $this->setLogger(new NullLogger());
    }

    public function hasFeature(string $target, string $feature): bool
    {
        if (!$this->registry->exists($feature)) {
            $this->logger->warning('Feature is unregistered', [
                'target' => $target,
                'feature' => $feature,
            ]);

            return false;
        }

        $expression = $this->registry->getDefinition($feature)->getStrategy();
        $values = array_map(
            fn (TogglingStrategy $strategy) => $strategy->decideIfTargetHasFeature($target, $feature),
            $this->strategies
        );

        try {
            return (bool) (new ExpressionLanguage())->evaluate($expression, $values);
        } catch (SyntaxError $error) {
            $this->logger->warning('Feature strategy is invalid', [
                'target' => $target,
                'feature' => $feature,
                'expression' => $expression,
                'values' => $values,
                'error' => $error->getMessage(),
            ]);

            return false;
        }
    }

    public function getFeatureConfiguration(string $feature): array
    {
        return array_map(
            fn (TogglingStrategy $strategy) => $strategy->getConfiguration($feature),
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

        $parameters = array_merge($parameters, [$feature]);

        $this->strategies[$strategy]->$method(...$parameters);

        $this->logger->info('Feature has been configured', [
            'feature' => $feature,
            'strategy' => $strategy,
            'method' => $method,
            'parameters' => $parameters,
        ]);
    }
}
