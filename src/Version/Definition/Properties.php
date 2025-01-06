<?php declare(strict_types=1);

namespace DCarbone\PHPFHIR\Version\Definition;

/*
 * Copyright 2016-2025 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use DCarbone\PHPFHIR\Enum\TypeKindEnum;

/**
 * Class Properties
 * @package DCarbone\PHPFHIR\Version\Definition\Type
 */
class Properties
{
    /** @var \DCarbone\PHPFHIR\Version\Definition\Property[] */
    private array $_properties = [];
    /** @var \DCarbone\PHPFHIR\Version\Definition\Property[] */
    private array $_sortedProperties;

    /** @var \DCarbone\PHPFHIR\Version\Definition\Property[] */
    private array $_localProperties;
    /** @var \DCarbone\PHPFHIR\Version\Definition\Property[] */
    private array $_localSortedProperties;

    /** @var bool */
    private bool $_cacheBuilt = false;

    /** @var \DCarbone\PHPFHIR\Version\Definition\Type */
    private Type $_type;

    /**
     * @param \DCarbone\PHPFHIR\Version\Definition\Type $type
     */
    public function __construct(Type $type)
    {
        $this->_type = $type;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return ['properties' => $this->_properties];
    }

    /**
     * @return \DCarbone\PHPFHIR\Version\Definition\Type
     */
    public function getType(): Type
    {
        return $this->_type;
    }

    /**
     * @param \DCarbone\PHPFHIR\Version\Definition\Property $property
     * @return \DCarbone\PHPFHIR\Version\Definition\Properties
     */
    public function addProperty(Property &$property): Properties
    {
        $pname = $property->getName();
        $pref = $property->getRef();
        if (null === $pname && null === $pref) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cannot add Property to Type "%s" as it has no $name or $ref defined',
                    $this->getType()->getFHIRName()
                )
            );
        }
        foreach ($this->_properties as $current) {
            if ($property === $current) {
                return $this;
            }
            $cname = $current->getName();
            $cref = $current->getRef();
            if (null !== $pname && null !== $cname && $pname === $cname) {
                $this->_type->getConfig()->getLogger()->notice(
                    sprintf(
                        'Type "%s" already has Property "%s" (name), probably some duplicate definition nonsense. Keeping original.',
                        $this->getType()->getFHIRName(),
                        $property->getName()
                    )
                );
                $property = $current;
                return $this;
            } elseif (null !== $pref && null !== $cref && $cref === $pref) {
                $this->_type->getConfig()->getLogger()->notice(
                    sprintf(
                        'Type "%s" already has Property "%s" (ref), probably some duplicate definition nonsense. Keeping original.',
                        $this->getType()->getFHIRName(),
                        $property->getRef()
                    )
                );
                $property = $current;
                return $this;
            }
        }
        $this->_properties[] = $property;
        $this->_cacheBuilt = false;
        return $this;
    }

    /**
     * @param string $name
     * @return \DCarbone\PHPFHIR\Version\Definition\Property|null
     */
    public function getProperty(string $name): ?Property
    {
        foreach ($this->_properties as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return null !== $this->getProperty($name);
    }

    /**
     * @return int
     */
    public function allPropertyCount(): int
    {
        return count($this->_properties);
    }

    /**
     * @return int
     */
    public function localPropertyCount(): int
    {
        $this->_buildCaches();
        return count($this->_localProperties);
    }

    /**
     * @return bool
     */
    public function hasLocalProperties(): bool
    {
        return $this->localPropertyCount() > 0;
    }

    /**
     * Returns an iterator containing all properties, including those inherited from parent types
     *
     * @return \DCarbone\PHPFHIR\Version\Definition\Property[]
     */
    public function getAllPropertiesIterator(): iterable
    {
        return new \ArrayIterator($this->_properties);
    }

    /**
     * @return \Generator
     */
    public function getAllPropertiesGenerator(): \Generator
    {
        foreach ($this->_properties as $p) {
            yield $p;
        }
    }

    /**
     * Returns an iterator contanining all properties, including those inherited from parent types, sorted ascending by name
     *
     * @return \DCarbone\PHPFHIR\Version\Definition\Property[]
     */
    public function getAllSortedPropertiesIterator(): iterable
    {
        $this->_buildCaches();
        return new \ArrayIterator($this->_sortedProperties);
    }

    /**
     * Returns an indexed iterator containing only properties local to this type.
     *
     * @return \DCarbone\PHPFHIR\Version\Definition\Property[]
     */
    public function getIndexedLocalPropertiesIterator(): iterable
    {
        $this->_buildCaches();
        return \SplFixedArray::fromArray($this->_localProperties, preserveKeys: false);
    }

    /**
     * Returns an iterator containing only properties local to this type.
     *
     * @return \DCarbone\PHPFHIR\Version\Definition\Property[]
     */
    public function getLocalPropertiesIterator(): iterable
    {
        $this->_buildCaches();
        return new \ArrayIterator($this->_localProperties);
    }

    /**
     * @return \Generator<\DCarbone\PHPFHIR\Version\Definition\Property>
     */
    public function getLocalPropertiesGenerator(): \Generator
    {
        $this->_buildCaches();
        foreach ($this->_localProperties as $p) {
            yield $p;
        }
    }

    /**
     * Returns an iterator containing only properties local to this type, sorted ascending by name
     *
     * @return \DCarbone\PHPFHIR\Version\Definition\Property[]
     */
    public function getLocalSortedPropertiesIterator(): iterable
    {
        $this->_buildCaches();
        return new \ArrayIterator($this->_localSortedProperties);
    }

    /**
     * @param \DCarbone\PHPFHIR\Enum\TypeKindEnum|null ...$kinds
     * @return \DCarbone\PHPFHIR\Version\Definition\Property[]
     */
    public function getLocalPropertiesOfTypeKinds(bool $includeCollections, null|TypeKindEnum...$kinds): iterable
    {
        $out = [];
        foreach ($this->getLocalPropertiesIterator() as $property) {
            if (!$includeCollections && $property->isCollection()) {
                continue;
            }
            $pt = $property->getValueFHIRType();
            if (in_array($pt?->getKind(), $kinds, true)) {
                $out[] = $property;
            }
        }
        return new \ArrayIterator($out);
    }

    private function _buildCaches(): void
    {
        if (!$this->_cacheBuilt) {
            $this->_sortedProperties = $this->_properties;
            $this->_localProperties = [];
            $this->_localSortedProperties = [];
            usort(
                $this->_sortedProperties,
                function (Property $a, Property $b) {
                    return strnatcmp($a->getName(), $b->getName());
                }
            );
            foreach ($this->_properties as $property) {
                if (!$property->isOverloaded()) {
                    $this->_localProperties[] = $property;
                }
            }
            foreach ($this->_sortedProperties as $property) {
                if (!$property->isOverloaded()) {
                    $this->_localSortedProperties[] = $property;
                }
            }
            $this->_cacheBuilt = true;
        }
    }
}