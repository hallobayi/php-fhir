<?php declare(strict_types=1);

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

use DCarbone\PHPFHIR\Enum\TypeKindEnum;

/** @var \DCarbone\PHPFHIR\Version\Definition\Property $property */

$propertyType = $property->getValueFHIRType();
$fieldConstantName = $property->getFieldConstantName();

$requireArgs = [
    'property' => $property
];

ob_start();

if ($propertyType->getKind()->isOneOf(TypeKindEnum::PRIMITIVE, TypeKindEnum::LIST) || $propertyType->hasPrimitiveParent()) :
    echo require_with(
        __DIR__ . DIRECTORY_SEPARATOR . 'property_setter_primitive.php',
        $requireArgs
    );
elseif ($propertyType->getKind() === TypeKindEnum::PRIMITIVE_CONTAINER || $propertyType->hasPrimitiveContainerParent() || $propertyType->isValueContainer()) :
    echo require_with(
        __DIR__ . DIRECTORY_SEPARATOR . 'property_setter_primitive_container.php',
        $requireArgs
    );
else :
    echo require_with(
        __DIR__ . DIRECTORY_SEPARATOR . 'property_setter_default.php',
        $requireArgs
    );
endif;

return ob_get_clean();
