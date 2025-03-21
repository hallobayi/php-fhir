<?php declare(strict_types=1);

/*
 * Copyright 2025 Daniel Carbone (daniel.p.carbone@gmail.com)
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

/** @var \DCarbone\PHPFHIR\Version $version */
/** @var \DCarbone\PHPFHIR\Version\Definition\Type $type; */

$typeKind = $type->getKind();

ob_start();

foreach($type->getProperties()->getIterator() as $property) :
    if (null !== $property->getOverloadedProperty()) {
        continue;
    }

    $propType = $property->getValueFHIRType();
    if (null === $propType) {
        continue;
    }

    $setter = $property->getSetterName();
    $propTypeKind = $propType->getKind();
    $propTypeClass = $propType->getClassName();
    $propName = $property->getName();
    $propNameExt = $property->getExtName();
    $propConst = $property->getFieldConstantName();
    $propConstExt = $property->getFieldConstantExtensionName();

    if ($propType->isPrimitiveType() || $propType->hasPrimitiveTypeParent()) : ?>
        if (isset($decoded-><?php echo $propName; ?>) || property_exists($decoded, self::<?php echo $propConst; ?>)) {
<?php   if ($property->isCollection()) : ?>
            if (is_array($decoded-><?php echo $property; ?>)) {
                foreach($decoded-><?php echo $propName; ?> as $v) {
                    $type-><?php echo $setter; ?>($v);
                }
            } else {
                $type-><?php echo $setter; ?>($decoded-><?php echo $propName; ?>);
                $type->_setJSONFieldElideSingletonArray(self::<?php echo $propConst; ?>, true);
            }
<?php   else : ?>
            $type-><?php echo $setter; ?>($decoded-><?php echo $propName; ?>);
<?php   endif; ?>
        }
<?php elseif ($propType->getKind() === TypeKindEnum::PHPFHIR_XHTML) : ?>
        if (isset($decoded-><?php echo $propName; ?>) || property_exists($decoded, self::<?php echo $propConst; ?>)) {
            $type-><?php echo $setter; ?>($decoded-><?php echo $propName; ?>);
        }
<?php elseif ($propType->isPrimitiveContainer() || $propType->hasPrimitiveContainerParent()) : ?>
        if (isset($decoded-><?php echo $propName; ?>)
            || isset($decoded-><?php echo $propNameExt; ?>)
            || property_exists($decoded, self::<?php echo $propConst; ?>)
            || property_exists($decoded, self::<?php echo $propConstExt; ?>)) {
<?php if ($property->isCollection()) : ?>
            $vals = (array)($decoded-><?php echo $propName; ?> ?? []);
            $exts = (array)($decoded-><?php echo $propConstExt; ?> ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type-><?php echo $setter; ?>(<?php echo $propTypeClass; ?>::jsonUnserialize($v, $config));
            }
<?php else : ?>
            $v = $decoded-><?php echo $propNameExt; ?> ?? new \stdClass();
            $v->value = $decoded-><?php echo $propName; ?> ?? null;
            $type-><?php echo $setter; ?>(<?php echo $propTypeClass; ?>::jsonUnserialize($v, $config));
<?php endif; ?>
        }
<?php
    // for contained resources, we must extract the resourceType key and construct the corresponding class directly
    elseif ($propTypeKind->isResourceContainer($version)) : ?>
        if (isset($decoded-><?php echo $propName; ?>)) {
<?php   if ($property->isCollection()) : ?>
            if (is_object($decoded-><?php echo $propName; ?>)) {
                $vals = [$decoded-><?php echo $propName; ?>];
                $type->_setJSONFieldElideSingletonArray(self::<?php echo $propConst; ?>, true);
            } else {
                $vals = $decoded-><?php echo $propName; ?>;
            }
            foreach($vals as $v) {
                $typeClassName = <?php echo PHPFHIR_VERSION_CLASSNAME_VERSION_TYPE_MAP; ?>::mustGetContainedTypeClassnameFromJSON($v);
                unset($v-><?php echo PHPFHIR_JSON_FIELD_RESOURCE_TYPE; ?>);
                $type-><?php echo $setter; ?>($typeClassName::jsonUnserialize($v, $config));
            }
<?php   else : ?>
            $typeClassName = <?php echo PHPFHIR_VERSION_CLASSNAME_VERSION_TYPE_MAP; ?>::mustGetContainedTypeClassnameFromJSON($decoded-><?php echo $propName; ?>);
            $v = $decoded-><?php echo $propName; ?>;
            unset($v-><?php echo PHPFHIR_JSON_FIELD_RESOURCE_TYPE; ?>);
            $type-><?php echo $setter; ?>($typeClassName::jsonUnserialize($v, $config));
<?php   endif; ?>
        }
<?php else : ?>
        if (isset($decoded-><?php echo $propName; ?>) || property_exists($decoded, self::<?php echo $propConst; ?>)) {
<?php   if ($property->isCollection()) : ?>
            if (is_object($decoded-><?php echo $propName; ?>)) {
                $vals = [$decoded-><?php echo $propName; ?>];
                $type->_setJSONFieldElideSingletonArray(self::<?php echo $propConst; ?>, true);
            } else {
                $vals = $decoded-><?php echo $propName; ?>;
            }
            foreach($vals as $v) {
                $type-><?php echo $setter; ?>(<?php echo $propTypeClass; ?>::jsonUnserialize($v, $config));
            }
<?php       else : ?>
            if (is_array($decoded-><?php echo $propName; ?>)) {
                $type-><?php echo $setter; ?>(<?php echo $propTypeClass; ?>::jsonUnserialize(reset($decoded-><?php echo $propName; ?>), $config));
            } else {
                $type-><?php echo $setter; ?>(<?php echo $propTypeClass; ?>::jsonUnserialize($decoded-><?php echo $propName; ?>, $config));
            }
<?php   endif; ?>
        }
<?php endif;
endforeach; ?>
        return $type;
    }
<?php return ob_get_clean();
