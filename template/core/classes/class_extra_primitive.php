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
 * Class <?php echo PHPFHIR_CLASSNAME_EXTRA_PRIMITIVE; if ('' !== $namespace) : ?>

 * @package \<?php echo $namespace; ?>
<?php endif; ?>

 */
final class <?php echo PHPFHIR_CLASSNAME_EXTRA_PRIMITIVE; ?>

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
        $this->_value = $value;
        return $this;
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
