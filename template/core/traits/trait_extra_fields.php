<?php declare(strict_types=1);

/*
 * Copyright 2024 Daniel Carbone (daniel.p.carbone@gmail.com)
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

$rootNS = $config->getFullyQualifiedName(false);

ob_start();
echo "<?php declare(strict_types=1);\n\n";

if ('' !== $rootNS) :
    echo "namespace {$rootNS};\n\n";
endif;

echo CopyrightUtils::getFullPHPFHIRCopyrightComment();

echo "\n\n";
?>
/**
 * Trait <?php echo PHPFHIR_TRAIT_EXTRA_FIELDS; if ('' !== $rootNS) : ?>

 * @package \<?php echo $rootNS; ?>
<?php endif; ?>

 */
trait <?php echo PHPFHIR_TRAIT_EXTRA_FIELDS; ?>

{
    /** @var array */
    private array $_extraFields = [];

    public function _hasExtraFields(): bool
    {
        return [] !== $this->_extraFields;
    }

    public function _setExtraField(string $name, mixed $value): void
    {
        $this->_extraFields[$name] = $value;
    }

    public function _getExtraField(string $name): mixed
    {
        if (array_key_exists($name, $this->_extraFields)) {
            return $this->_extraFields[$name];
        }
        trigger_error(sprintf('Warning: Undefined property: %s::$%s', get_called_class(), $name));
        return null;
    }

    public function _getExtraFieldNames(): array
    {
        return array_keys($this->_extraFields);
    }

    public function _getExtraFields(): array
    {
        return $this->_extraFields;
    }

    protected function _parseExtraFieldsFromArray(array $extra): void
    {
        foreach($extra as $name => $value) {
            if (null === $value || is_scalar($value)) {
                $this->_extraFields[$name] = new <?php echo PHPFHIR_CLASSNAME_EXTRA_PRIMITIVE; ?>($name, $value);
            } else {
                $this->_extraFields[$name] = new <?php echo PHPFHIR_CLASSNAME_EXTRA_COMPLEX; ?>($name, $value);
            }
        }
    }
}
<?php return ob_get_clean();
