<?php

namespace Symfony\Config\ZenstruckFoundry;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MakeFactoryConfig 
{
    private $defaultNamespace;
    private $addHints;
    private $_usedProperties = [];

    /**
     * Default namespace where factories will be created by maker.
     * @default 'Factory'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultNamespace($value): static
    {
        $this->_usedProperties['defaultNamespace'] = true;
        $this->defaultNamespace = $value;

        return $this;
    }

    /**
     * Add "beginner" hints in the created factory.
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function addHints($value): static
    {
        $this->_usedProperties['addHints'] = true;
        $this->addHints = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default_namespace', $config)) {
            $this->_usedProperties['defaultNamespace'] = true;
            $this->defaultNamespace = $config['default_namespace'];
            unset($config['default_namespace']);
        }

        if (array_key_exists('add_hints', $config)) {
            $this->_usedProperties['addHints'] = true;
            $this->addHints = $config['add_hints'];
            unset($config['add_hints']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['defaultNamespace'])) {
            $output['default_namespace'] = $this->defaultNamespace;
        }
        if (isset($this->_usedProperties['addHints'])) {
            $output['add_hints'] = $this->addHints;
        }

        return $output;
    }

}
