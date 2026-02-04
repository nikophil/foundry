<?php

namespace Symfony\Config\ZenstruckFoundry\Orm;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Reset'.\DIRECTORY_SEPARATOR.'MigrationsConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ResetConfig 
{
    private $connections;
    private $entityManagers;
    private $mode;
    private $migrations;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function connections(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['connections'] = true;
        $this->connections = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function entityManagers(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['entityManagers'] = true;
        $this->entityManagers = $value;

        return $this;
    }

    /**
     * Reset mode to use with ResetDatabase trait
     * @default \Zenstruck\Foundry\ORM\ResetDatabase\ResetDatabaseMode::SCHEMA
     * @param ParamConfigurator|\Zenstruck\Foundry\ORM\ResetDatabase\ResetDatabaseMode::SCHEMA|\Zenstruck\Foundry\ORM\ResetDatabase\ResetDatabaseMode::MIGRATE $value
     * @return $this
     */
    public function mode($value): static
    {
        $this->_usedProperties['mode'] = true;
        $this->mode = $value;

        return $this;
    }

    /**
     * @default {"configurations":[]}
     */
    public function migrations(array $value = []): \Symfony\Config\ZenstruckFoundry\Orm\Reset\MigrationsConfig
    {
        if (null === $this->migrations) {
            $this->_usedProperties['migrations'] = true;
            $this->migrations = new \Symfony\Config\ZenstruckFoundry\Orm\Reset\MigrationsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "migrations()" has already been initialized. You cannot pass values the second time you call migrations().');
        }

        return $this->migrations;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('connections', $config)) {
            $this->_usedProperties['connections'] = true;
            $this->connections = $config['connections'];
            unset($config['connections']);
        }

        if (array_key_exists('entity_managers', $config)) {
            $this->_usedProperties['entityManagers'] = true;
            $this->entityManagers = $config['entity_managers'];
            unset($config['entity_managers']);
        }

        if (array_key_exists('mode', $config)) {
            $this->_usedProperties['mode'] = true;
            $this->mode = $config['mode'];
            unset($config['mode']);
        }

        if (array_key_exists('migrations', $config)) {
            $this->_usedProperties['migrations'] = true;
            $this->migrations = new \Symfony\Config\ZenstruckFoundry\Orm\Reset\MigrationsConfig($config['migrations']);
            unset($config['migrations']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['connections'])) {
            $output['connections'] = $this->connections;
        }
        if (isset($this->_usedProperties['entityManagers'])) {
            $output['entity_managers'] = $this->entityManagers;
        }
        if (isset($this->_usedProperties['mode'])) {
            $output['mode'] = $this->mode;
        }
        if (isset($this->_usedProperties['migrations'])) {
            $output['migrations'] = $this->migrations->toArray();
        }

        return $output;
    }

}
