<?php declare(strict_types=1);

namespace DCarbone\PHPFHIR\Enum;

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

use DCarbone\PHPFHIR\Version\Definition\Property;
use DCarbone\PHPFHIR\Version\Definition\Type;

class XMLValueLocationUtils
{
    public static function determineDefaultLocation(Type $type, Property $property, bool $withClass): string
    {
        $propType = $property->getValueFHIRType();
        if ($propType->isPrimitiveOrListType() || $propType->hasPrimitiveContainerParent()) {
            $case = match(true) {
                $type->isPrimitiveContainer() || $type->hasPrimitiveContainerParent() => 'CONTAINER_ATTRIBUTE',
                default => 'ELEMENT_ATTRIBUTE',
            };
        } else if ($property->isValueProperty()) {
            $case = match (true) {
                $type->isQuantity() || $type->hasQuantityParent() => 'ELEMENT_ATTRIBUTE',
                default => 'CONTAINER_ATTRIBUTE',
            };
        } else {
            $case = 'ELEMENT_ATTRIBUTE';
        }
        if ($withClass) {
            return sprintf('%s::%s', PHPFHIR_ENCODING_ENUM_VALUE_XML_LOCATION, $case);
        }
        return $case;
    }
}