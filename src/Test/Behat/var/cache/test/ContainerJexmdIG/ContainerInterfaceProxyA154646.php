<?php

namespace ContainerJexmdIG;
include_once \dirname(__DIR__, 4).'/vendor/symfony/dependency-injection/ContainerInterface.php';

class ContainerInterfaceProxyA154646 implements \Symfony\Component\DependencyInjection\ContainerInterface, \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\Internal\LazyDecoratorTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [];

    public function initializeLazyObject(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        return $this->lazyObjectState->realInstance;
    }

    public function set(string $id, ?object $service): void
    {
        $this->lazyObjectState->realInstance->set(...\func_get_args());
    }

    public function get(string $id, int $invalidBehavior = \Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        ${0} = $this->lazyObjectState->realInstance;
        ${1} = ${0}->get(...\func_get_args());

        return match (true) {
            ${1} === ${0} => $this,
            !${1} instanceof ${0} || !${0} instanceof ${1} => ${1},
            null !== $this->lazyObjectState->cloneInstance =& ${1} => clone $this,
        };
    }

    public function has(string $id): bool
    {
        return $this->lazyObjectState->realInstance->has(...\func_get_args());
    }

    public function initialized(string $id): bool
    {
        return $this->lazyObjectState->realInstance->initialized(...\func_get_args());
    }

    public function getParameter(string $name): \UnitEnum|array|bool|float|int|null|string
    {
        return $this->lazyObjectState->realInstance->getParameter(...\func_get_args());
    }

    public function hasParameter(string $name): bool
    {
        return $this->lazyObjectState->realInstance->hasParameter(...\func_get_args());
    }

    public function setParameter(string $name, \UnitEnum|array|bool|float|int|null|string $value): void
    {
        $this->lazyObjectState->realInstance->setParameter(...\func_get_args());
    }
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);

if (!\class_exists('ContainerInterfaceProxyA154646', false)) {
    \class_alias(__NAMESPACE__.'\\ContainerInterfaceProxyA154646', 'ContainerInterfaceProxyA154646', false);
}
