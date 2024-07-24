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

use DCarbone\PHPFHIR\Utilities\NameUtils;

/** @var \DCarbone\PHPFHIR\Config\VersionConfig $config */
/** @var \DCarbone\PHPFHIR\Definition\Types $types */
/** @var \DCarbone\PHPFHIR\Definition\Type $type */

$fqns = $type->getFullyQualifiedNamespace(true);
$classDocumentation = $type->getDocBlockDocumentationFragment(1, true);
$namespace = trim($fqns, PHPFHIR_NAMESPACE_TRIM_CUTSET);
$xmlName = NameUtils::getTypeXMLElementName($type);

ob_start();

// build file header
echo require_with(
    PHPFHIR_TEMPLATE_FILE_DIR . DIRECTORY_SEPARATOR . 'header_type.php',
    [
        'config' => $config,
        'fqns' => $fqns,
        'skipImports' => false,
        'type' => $type,
        'types' => $types,
    ]
);

// build class header ?>
/**<?php if ('' !== $classDocumentation) : ?>

<?php echo $classDocumentation; ?>
 *<?php endif; ?>

 * Class <?php echo $type->getClassName(); ?>

 * @package <?php echo $fqns; ?>

 */
class <?php echo $type->getClassName(); ?>

{
    /** @var string */
    private string $_name;
    /** @var null|string|bool|int|float $_value */
    private null|string|bool|int|float $_value;

    /**
     * @param string $name
     * @param null|string|bool|int|float $value
     */
    public function __construct(string $name, null|string|bool|int|float $value)
    {
        $this->_name = $name;
        $this->_value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->_name ?? '';
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * @return null|string|bool|int|float
     */
    public function getValue(): null|string|bool|int|float
    {
        return $this->_value ?? null;
    }

    /**
     * @param null|string|bool|int|float $value
     * @return self
     */
    public function setValue(null|string|bool|int|float $value): self
    {
        $this->_trackValueSet($this->_value ?? null, $value);
        $this->_value = $value;
        return $this;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        $out->{$this->getName()} = $this->getValue();
        return $out;
    }

<?php
// unserialize portion
echo require_with(
        PHPFHIR_TEMPLATE_TYPES_SERIALIZATION_DIR . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'unserialize' . DIRECTORY_SEPARATOR . 'header.php',
    [
        'config' => $config,
        'type' => $type,
        'typeKind' => $type->getKind(),
        'parentType' => null,
        'typeClassName' => $type->getClassName()
    ]
);
?>
        $type->setValue((string)$element);
        return $type;
    }

<?php echo require_with(
    PHPFHIR_TEMPLATE_TYPES_SERIALIZATION_DIR . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'serialize' . DIRECTORY_SEPARATOR . 'header.php',
    [
        'config' => $config,
        'type' => $type,
    ]
); ?>
        $xw->writeAttribute($this->getName(), $this->getValue());
        if (isset($rootOpened) && $rootOpened) {
            $xw->endElement();
        }
        if (isset($docStarted) && $docStarted) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getValue();
    }
}
<?php return ob_get_clean();
