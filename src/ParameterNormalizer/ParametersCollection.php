<?php

namespace Zenstruck\Foundry\ParameterNormalizer;

use Zenstruck\Foundry\Factory;

/**
 * @phpstan-import-type Parameters from Factory
 */
final class ParametersCollection
{
    /** @phpstan-var Parameters */
    private array $parameters = [];

    /** @var list<callable(object): void> */
    private array $afterInstantiateCallbacks = [];

    public function addParameter(string $field, Parameter $parameter): void
    {
        $this->parameters[$field] = $parameter->value;

        if ($parameter->afterInstantiateCallback) {
            $this->afterInstantiateCallbacks[] = $parameter->afterInstantiateCallback;
        }
    }

    /**
     * @phpstan-return Parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return list<callable(object): void>
     */
    public function getAfterInstantiateCallbacks(): array
    {
        return $this->afterInstantiateCallbacks;
    }
}
