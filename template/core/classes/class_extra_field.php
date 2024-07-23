<?php declare(strict_types=1);

/*
 * Copyright 2018-2024 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use DCarbone\PHPFHIR\Utilities\CopyrightUtils;

/** @var \DCarbone\PHPFHIR\Config\VersionConfig $config */
/** @var \DCarbone\PHPFHIR\Definition\Types $types */

$namespace = $config->getFullyQualifiedName(false);

ob_start();

echo "<?php declare(strict_types=1);\n\n";

if ('' !== $namespace) :
    echo "namespace {$namespace};\n\n";
endif;

echo CopyrightUtils::getFullPHPFHIRCopyrightComment();

echo "\n\n"; ?>
/**
 * Class <?php echo PHPFHIR_CLASSNAME_EXTRA_FIELD; if ('' !== $namespace) : ?>

 * @package \<?php echo $namespace; ?>
<?php endif; ?>

 */
final class <?php echo PHPFHIR_CLASSNAME_EXTRA_FIELD; ?> implements \JsonSerializable
{
    /** @var string */
    private string $_name;
    /** @var mixed */
    private mixed $_value;
    /** @var <?php echo $config->getFullyQualifiedName(true, PHPFHIR_ENUM_XML_LOCATION_ENUM); ?> */
    private <?php echo PHPFHIR_ENUM_XML_LOCATION_ENUM; ?> $_xmlLoc;

    public function __construct(string $name, mixed $value, <?php echo PHPFHIR_ENUM_XML_LOCATION_ENUM; ?> $xmlLoc = <?php echo PHPFHIR_ENUM_XML_LOCATION_ENUM; ?>::ATTRIBUTE)
    {
        $this->_name = $name;
        $this->_value = $value;
        $this->_xmlLoc = $xmlLoc;
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function getValue(): mixed
    {
        return $this->_value;
    }
}
<?php return ob_get_clean();
