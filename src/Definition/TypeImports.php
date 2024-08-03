<?php declare(strict_types=1);

namespace DCarbone\PHPFHIR\Definition;

/*
 * Copyright 2016-2024 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use DCarbone\PHPFHIR\Enum\TypeKind;
use DCarbone\PHPFHIR\Utilities\NameUtils;

/**
 * Class TypeImports
 * @package DCarbone\PHPFHIR\Definition
 */
class TypeImports implements \Countable
{
    /** @var \DCarbone\PHPFHIR\Definition\Type */
    private Type $type;

    /** @var \DCarbone\PHPFHIR\Definition\TypeImport[] */
    private array $imports = [];
    /** @var bool */
    private bool $parsed = false;

    /**
     * TypeImports constructor.
     * @param \DCarbone\PHPFHIR\Definition\Type $type
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    /**
     * @param \DCarbone\PHPFHIR\Definition\Type $type
     * @return \DCarbone\PHPFHIR\Definition\TypeImport|null
     */
    public function getImportByType(Type $type): ?TypeImport
    {
        $this->buildImports();
        $fqn = $type->getFullyQualifiedClassName(false);
        foreach ($this->imports as $import) {
            if ($import->getFullyQualifiedClassname(false) === $fqn) {
                return $import;
            }
        }
        return null;
    }

    /**
     * @param string $aliasName
     * @return \DCarbone\PHPFHIR\Definition\TypeImport|null
     */
    public function getImportByAlias(string $aliasName): ?TypeImport
    {
        $this->buildImports();
        return $this->imports[$aliasName] ?? null;
    }

    /**
     * @return \DCarbone\PHPFHIR\Definition\TypeImport[]
     */
    public function getIterator(): \Traversable
    {
        $this->buildImports();
        return new \ArrayIterator($this->imports);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        if (!$this->parsed) {
            $this->buildImports();
        }
        return count($this->imports);
    }

    /**
     * TODO: come up with better alias scheme...
     *
     * @param string $classname
     * @return string
     */
    private function findNextAliasName(string $classname): string
    {
        $i = 1;
        $aliasName = "{$classname}{$i}";
        while (null !== $this->getImportByAlias($aliasName)) {
            $aliasName = "{$classname}{++$i}";
        }
        return $aliasName;
    }

    /**
     * @param string $classname
     * @param string $namespace
     */
    private function addImport(string $classname, string $namespace): void
    {
        $requiresImport = !str_starts_with($classname, '\\') && $namespace !== $this->type->getFullyQualifiedNamespace(false);
        if (isset($this->imports[$classname])) {
            // if we have already seen this type, move on.
            if ($this->imports[$classname]->getNamespace() === $namespace) {
                return;
            }

            // if there is a conflicting imported type here...
            $aliasName = $this->findNextAliasName($classname);
            $this->imports[$aliasName] = new TypeImport($classname, $namespace, true, $aliasName, $requiresImport);
            return;
        }

        if ($classname === $this->type->getClassName() &&
            $namespace !== $this->type->getFullyQualifiedNamespace(false)) {
            // if the imported type has the same class name as the direct type, but a different namespace
            $aliasName = $this->findNextAliasName($classname);
            $this->imports[$aliasName] = new TypeImport($classname, $namespace, true, $aliasName, $requiresImport);
            return;
        }

        // otherwise, go ahead and add to map.
        $this->imports[$classname] = new TypeImport($classname, $namespace, false, '', $requiresImport);
    }

    private function buildImports(): void
    {
        // safety dance
        if ($this->parsed) {
            return;
        }

        // immediately set to true so we don't repeat ourselves to death.
        $this->parsed = true;

        // immediately add self
        $this->addImport($this->type->getClassName(), $this->type->getFullyQualifiedNamespace(false));

        $typeNS = $this->type->getFullyQualifiedNamespace(false);
        $rootNS = $this->type->getConfig()->getFullyQualifiedName(false);

        $sortedProperties = $this->type->getAllPropertiesIterator();

        // non-abstract types must import some basics
        if (!$this->type->isAbstract()) {
            $this->addImport(PHPFHIR_CLASSNAME_CONFIG, $rootNS);
            $this->addImport(PHPFHIR_CLASSNAME_XML_WRITER, $rootNS);
            $this->addImport(PHPFHIR_ENUM_CONFIG_KEY, $rootNS);
            $this->addImport(PHPFHIR_ENUM_XML_LOCATION_ENUM, $rootNS);

            $this->addImport(NameUtils::getTypeClassName(PHPFHIR_EXTRA_PRIMITVE_TYPE), $rootNS);
            $this->addImport(NameUtils::getTypeClassName(PHPFHIR_EXTRA_COMPLEX_TYPE), $rootNS);
        }

        // if this type is in a nested namespace, there are  a few base interfaces, classes, and traits
        // that may need to be imported to ensure function
        if ($typeNS !== $rootNS) {
            // always add the base interface type as its used by the xml serialization func
            $this->addImport(PHPFHIR_INTERFACE_TYPE, $rootNS);
            // always add the constants class as its used everywhere.
            $this->addImport(PHPFHIR_CLASSNAME_CONSTANTS, $rootNS);
            // add directly implemented interfaces
            foreach ($this->type->getDirectlyImplementedInterfaces() as $interface) {
                $this->addImport($interface, $rootNS);
            }
            // add directly implemented traits
            foreach ($this->type->getDirectlyUsedTraits() as $trait) {
                $this->addImport($trait, $rootNS);
            }
        }

        // determine if we need to import our parent type
        if ($parentType = $this->type->getParentType()) {
            $this->addImport($parentType->getClassName(), $parentType->getFullyQualifiedNamespace(false));
        }

        // determine if we need to import a restriction base
        if ($restrictionBaseType = $this->type->getRestrictionBaseFHIRType()) {
            $rns = $restrictionBaseType->getFullyQualifiedNamespace(false);
            $this->addImport($restrictionBaseType->getClassName(), $rns);
        }

        // add property types to import statement
        foreach ($sortedProperties as $property) {
            $propertyType = $property->getValueFHIRType();
            if (null === $propertyType) {
                continue;
            }

            $ptk = $propertyType->getKind();

            if ($ptk->isOneOf(TypeKind::RESOURCE_CONTAINER, TypeKind::RESOURCE_INLINE) &&
                $typeNS !== $rootNS) {
                $this->addImport(PHPFHIR_INTERFACE_CONTAINED_TYPE, $rootNS);
                $this->addImport(PHPFHIR_CLASSNAME_TYPEMAP, $rootNS);
            } else {

                if ($ptk === TypeKind::PRIMITIVE_CONTAINER) {
                    $primType = $propertyType->getLocalProperties()->getProperty('value')->getValueFHIRType();
                    $this->addImport($primType->getClassName(), $primType->getFullyQualifiedNamespace(false));
                }

                $propertyTypeNS = $propertyType->getFullyQualifiedNamespace(false);
                $this->addImport($propertyType->getClassName(), $propertyTypeNS);
            }
        }

        // sort the imported class list
        uasort(
            $this->imports,
            function (TypeImport $a, TypeImport $b) {
                return strnatcasecmp($a->getFullyQualifiedClassname(false), $b->getFullyQualifiedClassname(false));
            }
        );
    }
}