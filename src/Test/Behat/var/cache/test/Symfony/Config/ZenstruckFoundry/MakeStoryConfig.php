<?php

namespace Symfony\Config\ZenstruckFoundry;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MakeStoryConfig 
{
    private $defaultNamespace;
    private $_usedProperties = [];

    /**
     * Default namespace where stories will be created by maker.
     * @default 'Story'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultNamespace($value): static
    {
        $this->_usedProperties['defaultNamespace'] = true;
        $this->defaultNamespace = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default_namespace', $config)) {
            $this->_usedProperties['defaultNamespace'] = true;
            $this->defaultNamespace = $config['default_namespace'];
            unset($config['default_namespace']);
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

        return $output;
    }

}
