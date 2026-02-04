<?php

namespace Symfony\Config\ZenstruckFoundry\Orm\Reset;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MigrationsConfig 
{
    private $configurations;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function configurations(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['configurations'] = true;
        $this->configurations = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('configurations', $config)) {
            $this->_usedProperties['configurations'] = true;
            $this->configurations = $config['configurations'];
            unset($config['configurations']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['configurations'])) {
            $output['configurations'] = $this->configurations;
        }

        return $output;
    }

}
