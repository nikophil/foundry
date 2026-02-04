<?php

namespace Symfony\Config\ZenstruckFoundry;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PersistenceConfig 
{
    private $flushOnce;
    private $_usedProperties = [];

    /**
     * Flush only once per call of `PersistentObjectFactory::create()` in userland.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function flushOnce($value): static
    {
        $this->_usedProperties['flushOnce'] = true;
        $this->flushOnce = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('flush_once', $config)) {
            $this->_usedProperties['flushOnce'] = true;
            $this->flushOnce = $config['flush_once'];
            unset($config['flush_once']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['flushOnce'])) {
            $output['flush_once'] = $this->flushOnce;
        }

        return $output;
    }

}
