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

/** @var \DCarbone\PHPFHIR\Config\VersionConfig $config */
/** @var \DCarbone\PHPFHIR\Definition\Types $types */
/** @var \DCarbone\PHPFHIR\Definition\Type $type */

$fqns = $type->getFullyQualifiedNamespace(true);
$classDocumentation = $type->getDocBlockDocumentationFragment(1, true);
$namespace = trim($fqns, PHPFHIR_NAMESPACE_TRIM_CUTSET);
$extraPrimitiveClassname = $types->getTypeByName(PHPFHIR_EXTRA_PRIMITVE_TYPE)->getClassName();

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
    private string $_name;
    private array $_attributes = [];
    private array $_children = [];

    public function __construct(string $name)
    {
        $this->_name = $name;
    }

    public static function fromArray(string $name, array $field): <?php echo $type->getClassName(); ?>

    {
        $type = new  <?php echo $type->getClassName(); ?>($name);
        foreach($field as $k => $v) {
            // null values must be skipped as their position in output is ambiguous
            if (null === $v) {
                continue;
            }
            if (is_scalar($v) || $v instanceof <?php echo $extraPrimitiveClassname; ?>) {
                $type->addAttribute($k, $v);
            } elseif (is_array($v) || $v instanceof \stdClass || $v instanceof <?php echo $type->getClassName(); ?>) {
                $type->addChild($k, $v);
            }
        }
        return $type;
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function getAttributes(): array
    {
        return $this->_attributes;
    }

    public function addAttribute(string $k, null|string|bool|int|float|<?php echo $extraPrimitiveClassname; ?> $v): self
    {
        if (!($v instanceof <?php echo $extraPrimitiveClassname; ?>)) {
            $v = new <?php echo $extraPrimitiveClassname; ?>($k, $v);
        }
        $this->_attributes[$k] = $v;
        return $this;
    }

    public function getChildren(): array
    {
        return $this->_children;
    }

    public function addChild(string $k, null|array|\stdClass|<?php echo $type->getClassName(); ?> $child): self
    {
        if (null === $child || $child instanceof <?php echo $type->getClassName(); ?>) {
            $this->_children[$k] = $child;
            return $this;
        }
        if ($child instanceof \stdClass) {
            $child = (array)$child;
        }
        foreach($child as $field => $value) {
            if ($value instanceof <?php echo $extraPrimitiveClassname; ?> || $value instanceof <?php echo $type->getClassName(); ?>) {
                $this->_children[$field] = $value;
            } elseif (is_scalar($value)) {
                $this->_children[$field] = new <?php echo $extraPrimitiveClassname; ?>($field, $value);
            } else {
                if ($value instanceof \stdClass) {
                    $value = (array)$value;
                }
                if (is_array($value) && is_int(key($value))) {
                    $this->_children[$field] = [];
                    foreach($value as $k => $v) {
                        $this->_children[$field][] = <?php echo $type->getClassName(); ?>::fromArray($k, $v);
                    }
                } else {
                    $this->_children[$field] = <?php echo $type->getClassName(); ?>::fromArray($field, $value);
                }
            }
        }
        return $this;
    }

    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        foreach($this->_attributes as $k => $v) {
            $out->{$k} = $v;
        }
        foreach($this->_children as $k => $v) {
            $out->{$k} = $v;
        }
        return $out;
    }
}
<?php return ob_get_clean();
