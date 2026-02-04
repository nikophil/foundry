<?php

namespace Symfony\Config\ZenstruckFoundry\Mongo;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ResetConfig 
{
    private $documentManagers;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function documentManagers(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['documentManagers'] = true;
        $this->documentManagers = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('document_managers', $config)) {
            $this->_usedProperties['documentManagers'] = true;
            $this->documentManagers = $config['document_managers'];
            unset($config['document_managers']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['documentManagers'])) {
            $output['document_managers'] = $this->documentManagers;
        }

        return $output;
    }

}
