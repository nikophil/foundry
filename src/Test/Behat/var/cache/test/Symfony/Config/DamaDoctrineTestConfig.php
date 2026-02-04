<?php

namespace Symfony\Config;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DamaDoctrineTestConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $enableStaticConnection;
    private $enableStaticMetaDataCache;
    private $enableStaticQueryCache;
    private $connectionKeys;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default true
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function enableStaticConnection(mixed $value = true): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['enableStaticConnection'] = true;
        $this->enableStaticConnection = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function enableStaticMetaDataCache($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['enableStaticMetaDataCache'] = true;
        $this->enableStaticMetaDataCache = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function enableStaticQueryCache($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['enableStaticQueryCache'] = true;
        $this->enableStaticQueryCache = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function connectionKeys(ParamConfigurator|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['connectionKeys'] = true;
        $this->connectionKeys = $value;

        return $this;
    }

    public function getExtensionAlias(): string
    {
        return 'dama_doctrine_test';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enable_static_connection', $config)) {
            $this->_usedProperties['enableStaticConnection'] = true;
            $this->enableStaticConnection = $config['enable_static_connection'];
            unset($config['enable_static_connection']);
        }

        if (array_key_exists('enable_static_meta_data_cache', $config)) {
            $this->_usedProperties['enableStaticMetaDataCache'] = true;
            $this->enableStaticMetaDataCache = $config['enable_static_meta_data_cache'];
            unset($config['enable_static_meta_data_cache']);
        }

        if (array_key_exists('enable_static_query_cache', $config)) {
            $this->_usedProperties['enableStaticQueryCache'] = true;
            $this->enableStaticQueryCache = $config['enable_static_query_cache'];
            unset($config['enable_static_query_cache']);
        }

        if (array_key_exists('connection_keys', $config)) {
            $this->_usedProperties['connectionKeys'] = true;
            $this->connectionKeys = $config['connection_keys'];
            unset($config['connection_keys']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['enableStaticConnection'])) {
            $output['enable_static_connection'] = $this->enableStaticConnection;
        }
        if (isset($this->_usedProperties['enableStaticMetaDataCache'])) {
            $output['enable_static_meta_data_cache'] = $this->enableStaticMetaDataCache;
        }
        if (isset($this->_usedProperties['enableStaticQueryCache'])) {
            $output['enable_static_query_cache'] = $this->enableStaticQueryCache;
        }
        if (isset($this->_usedProperties['connectionKeys'])) {
            $output['connection_keys'] = $this->connectionKeys;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
