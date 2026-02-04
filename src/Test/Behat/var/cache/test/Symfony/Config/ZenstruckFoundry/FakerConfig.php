<?php

namespace Symfony\Config\ZenstruckFoundry;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FakerConfig 
{
    private $locale;
    private $seed;
    private $manageSeed;
    private $service;
    private $_usedProperties = [];

    /**
     * The default locale to use for faker.
     * @example fr_FR
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function locale($value): static
    {
        $this->_usedProperties['locale'] = true;
        $this->locale = $value;

        return $this;
    }

    /**
     * Random number generator seed to produce the same fake values every run.
     * @example 1234
     * @default null
     * @param ParamConfigurator|mixed $value
     * @deprecated Since zenstruck/foundry 2.4: The "faker.seed" configuration is deprecated and will be removed in 3.0. Use environment variable "FOUNDRY_FAKER_SEED" instead.
     * @return $this
     */
    public function seed($value): static
    {
        $this->_usedProperties['seed'] = true;
        $this->seed = $value;

        return $this;
    }

    /**
     * Automatically manage faker seed to ensure consistent data between test runs.
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function manageSeed($value): static
    {
        $this->_usedProperties['manageSeed'] = true;
        $this->manageSeed = $value;

        return $this;
    }

    /**
     * Service id for custom faker instance.
     * @example my_faker
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
        if (array_key_exists('locale', $config)) {
            $this->_usedProperties['locale'] = true;
            $this->locale = $config['locale'];
            unset($config['locale']);
        }

        if (array_key_exists('seed', $config)) {
            $this->_usedProperties['seed'] = true;
            $this->seed = $config['seed'];
            unset($config['seed']);
        }

        if (array_key_exists('manage_seed', $config)) {
            $this->_usedProperties['manageSeed'] = true;
            $this->manageSeed = $config['manage_seed'];
            unset($config['manage_seed']);
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
        if (isset($this->_usedProperties['locale'])) {
            $output['locale'] = $this->locale;
        }
        if (isset($this->_usedProperties['seed'])) {
            $output['seed'] = $this->seed;
        }
        if (isset($this->_usedProperties['manageSeed'])) {
            $output['manage_seed'] = $this->manageSeed;
        }
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }

        return $output;
    }

}
