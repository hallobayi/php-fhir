<?php declare(strict_types=1);

namespace DCarbone\PHPFHIR\Version;

/*
 * Copyright 2018-2025 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class DefaultConfig
{
    private const _UNSERIALIZE_CONFIG_KEYS = [
        'libxmlOpts',
        'libxmlOptMask',
        'jsonDecodeMaxDepth',
    ];

    private const _SERIALIZE_CONFIG_KEYS = [
        'overrideSourceXMLNS',
        'rootXMLNS',
    ];

    /** @var array */
    private array $_unserializeConfig = [];
    /** @var array */
    private array $_serializeConfig = [];

    /**
     * @param array $unserializeConfig
     * @param array $serializeConfig
     */
    public function __construct(array $unserializeConfig = [],
                                array $serializeConfig = [])
    {
        $this->setUnserializeConfig($unserializeConfig);
        $this->setSerializeConfig($serializeConfig);
    }

    /**
     * @param array $config
     * @return self
     */
    public function setUnserializeConfig(array $config): self
    {
        $this->_unserializeConfig = [];
        if ([] === $config) {
            return $this;
        }
        if (isset($config['libxmlOpts']) && isset($config['libxmlOptMask'])) {
            throw new \DomainException('Cannot specify both "libxmlOpts" and "libxmlOptMask" keys.');
        }
        foreach (self::_UNSERIALIZE_CONFIG_KEYS as $k) {
            if (isset($config[$k]) || array_key_exists($k, $config)) {
                $this->_unserializeConfig[$k] = $config[$k];
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getUnserializeConfig(): array
    {
        return $this->_unserializeConfig;
    }

    /**
     * @param array $config
     * @return self
     */
    public function setSerializeConfig(array $config): self
    {
        $this->_serializeConfig = [];
        if ([] === $config) {
            return $this;
        }
        foreach (self::_SERIALIZE_CONFIG_KEYS as $k) {
            if (isset($config[$k]) || array_key_exists($k, $config)) {
                $this->_serializeConfig[$k] = $config[$k];
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getSerializeConfig(): array
    {
        return $this->_serializeConfig;
    }

    public function toArray(): array
    {
        return [
            'serializeConfig' => $this->getSerializeConfig(),
            'unserializeConfig' => $this->getUnserializeConfig(),
        ];
    }
}