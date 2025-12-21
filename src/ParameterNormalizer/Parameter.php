<?php

namespace Zenstruck\Foundry\ParameterNormalizer;

final class Parameter
{
    public function __construct(
        public readonly mixed $value,
        /** @var \Closure(object): void|null */
        public readonly \Closure|null $afterInstantiateCallback = null,
    ) {
        if ($this->value instanceof self) {
            throw new \LogicException('Cannot nest parameters.');
        }
    }
}
