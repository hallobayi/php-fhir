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
final class <?php echo $type->getClassName(); ?> implements \JsonSerializable
{
    public string $name;
    public array $attributes;
    public array $children;

    public function __construct(string $name, array $attributes = [], array $children = [])
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(string $k, mixed $v): self
    {
        $this->attributes[$k] = $v;
        return $this;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(string $k, <?php echo $type->getClassName(); ?> $v): self
    {
        $this->children[$k] = $v;
        return $this;
    }

    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        foreach($this->attributes as $k => $v) {
            $out->{$k} = $v;
        }
        foreach($this->children as $k => $v) {
            $out->{$k} = $v;
        }
        return $out;
    }
}
<?php return ob_get_clean();
