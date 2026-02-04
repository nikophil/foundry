<?php

namespace Symfony\Config\ZenstruckFoundry;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class InstantiatorConfig 
{
    private $useConstructor;
    private $allowExtraAttributes;
    private $alwaysForceProperties;
    private $service;
    private $_usedProperties = [];

    /**
     * Use the constructor to instantiate objects.
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function useConstructor($value): static
    {
        $this->_usedProperties['useConstructor'] = true;
        $this->useConstructor = $value;

        return $this;
    }

    /**
     * Whether or not to skip attributes that do not correspond to properties.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowExtraAttributes($value): static
    {
        $this->_usedProperties['allowExtraAttributes'] = true;
        $this->allowExtraAttributes = $value;

        return $this;
    }

    /**
     * Whether or not to skip setters and force set object properties (public/private/protected) directly.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function alwaysForceProperties($value): static
    {
        $this->_usedProperties['alwaysForceProperties'] = true;
        $this->alwaysForceProperties = $value;

        return $this;
    }

    /**
     * Service id of your custom instantiator.
     * @example my_instantiator
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function service($value): static
    {
        $this->_usedProperties['service'] = true;
        $this->service = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('use_constructor', $config)) {
            $this->_usedProperties['useConstructor'] = true;
            $this->useConstructor = $config['use_constructor'];
            unset($config['use_constructor']);
        }

        if (array_key_exists('allow_extra_attributes', $config)) {
            $this->_usedProperties['allowExtraAttributes'] = true;
            $this->allowExtraAttributes = $config['allow_extra_attributes'];
            unset($config['allow_extra_attributes']);
        }

        if (array_key_exists('always_force_properties', $config)) {
            $this->_usedProperties['alwaysForceProperties'] = true;
            $this->alwaysForceProperties = $config['always_force_properties'];
            unset($config['always_force_properties']);
        }

        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['useConstructor'])) {
            $output['use_constructor'] = $this->useConstructor;
        }
        if (isset($this->_usedProperties['allowExtraAttributes'])) {
            $output['allow_extra_attributes'] = $this->allowExtraAttributes;
        }
        if (isset($this->_usedProperties['alwaysForceProperties'])) {
            $output['always_force_properties'] = $this->alwaysForceProperties;
        }
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }

        return $output;
    }

}
