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
final class <?php echo $type->getClassName(); ?> implements \JsonSerializable
{
    private const _NUMRE = '/^[0-9,.-]+$/';

    private string $_name;
    private null|string|bool|int|float $_value = null;

    private bool $_commas = false;
    private int $_decimals = 0;

    public function __construct(string $name, null|string|bool|int|float $value = null)
    {
        $this->_name = $name;
        $this->setValue($value);
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function getValue(): null|string|bool|int|float
    {
        return $this->_value;
    }

    public function setValue(null|string|bool|int|float $value): self
    {
        if (!is_string($value)) {
            $this->_value = $value;
            return $this;
        }
        if (($l = strtolower($value)) && (<?php echo PHPFHIR_CLASSNAME_CONSTANTS; ?>::STRING_TRUE === $l || <?php echo PHPFHIR_CLASSNAME_CONSTANTS; ?>::STRING_FALSE === $l)) {
            $this->_value = $l === <?php echo PHPFHIR_CLASSNAME_CONSTANTS; ?>::STRING_TRUE;
            return $this;
        }
        if (!preg_match(self::_NUMRE, $value)) {
            $this->_value = $value;
            return $this;
        }
        if ($this->_commas = str_contains($value, ',')) {
            $value = str_replace(',', '', $value);
        }
        $dec = strstr($value, '.');
        if (false === $dec) {
            $this->_decimals = 0;
            $this->_value = intval($value);
        } else {
            $this->_decimals = strlen($dec) - 1;
            $this->_value = floatval($value);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->_value;
    }

    /**
     * @return string
     */
    public function getFormattedValue(): string
    {
        $v = $this->getValue();
        if (null === $v || is_string($v)) {
            return $v;
        }
        if (is_bool($v)) {
            return $v ? <?php echo PHPFHIR_CLASSNAME_CONSTANTS; ?>::STRING_TRUE : <?php echo PHPFHIR_CLASSNAME_CONSTANTS; ?>::STRING_FALSE;
        }
        if (is_float($v)) {
            return number_format($v, $this->_decimals, '.', $this->_commas ? ',' : '');
        }
        if ($this->_commas) {
            return strrev(wordwrap(strrev((string)$v), 3, ',', true));
        }
        return (string)$v;
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
