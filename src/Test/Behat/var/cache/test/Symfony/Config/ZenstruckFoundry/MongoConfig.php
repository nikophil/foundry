<?php

namespace Symfony\Config\ZenstruckFoundry;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Mongo'.\DIRECTORY_SEPARATOR.'ResetConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MongoConfig 
{
    private $autoPersist;
    private $reset;
    private $_usedProperties = [];

    /**
     * Automatically persist documents when created.
     * @default true
     * @param ParamConfigurator|bool $value
     * @deprecated Since zenstruck/foundry 2.4: Since 2.4 auto_persist defaults to true and this configuration has no effect.
     * @return $this
     */
    public function autoPersist($value): static
    {
        $this->_usedProperties['autoPersist'] = true;
        $this->autoPersist = $value;

        return $this;
    }

    /**
     * @default {"document_managers":["default"]}
     */
    public function reset(array $value = []): \Symfony\Config\ZenstruckFoundry\Mongo\ResetConfig
    {
        if (null === $this->reset) {
            $this->_usedProperties['reset'] = true;
            $this->reset = new \Symfony\Config\ZenstruckFoundry\Mongo\ResetConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "reset()" has already been initialized. You cannot pass values the second time you call reset().');
        }

        return $this->reset;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('auto_persist', $config)) {
            $this->_usedProperties['autoPersist'] = true;
            $this->autoPersist = $config['auto_persist'];
            unset($config['auto_persist']);
        }

        if (array_key_exists('reset', $config)) {
            $this->_usedProperties['reset'] = true;
            $this->reset = new \Symfony\Config\ZenstruckFoundry\Mongo\ResetConfig($config['reset']);
            unset($config['reset']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['autoPersist'])) {
            $output['auto_persist'] = $this->autoPersist;
        }
        if (isset($this->_usedProperties['reset'])) {
            $output['reset'] = $this->reset->toArray();
        }

        return $output;
    }

}
