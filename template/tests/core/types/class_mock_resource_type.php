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

use DCarbone\PHPFHIR\Utilities\ImportUtils;

/** @var \DCarbone\PHPFHIR\Config $config */
/** @var \DCarbone\PHPFHIR\CoreFile $coreFile */

$coreFiles = $config->getCoreFiles();
$testCoreFiles = $config->getCoreTestFiles();
$imports = $coreFile->getImports();

$resourceTypeInterface = $coreFiles->getCoreFileByEntityName(PHPFHIR_TYPES_INTERFACE_RESOURCE_TYPE);
$commentContainerInterface = $coreFiles->getCoreFileByEntityName(PHPFHIR_TYPES_INTERFACE_COMMENT_CONTAINER);
$commentContainerTrait = $coreFiles->getCoreFileByEntityName(PHPFHIR_TYPES_TRAIT_COMMENT_CONTAINER);
$sourceXMLNSTrait = $coreFiles->getCoreFileByEntityName(PHPFHIR_TYPES_TRAIT_SOURCE_XMLNS);

$typeValidationTrait = $coreFiles->getCoreFileByEntityName(PHPFHIR_VALIDATION_TRAIT_TYPE_VALIDATIONS);

$jsonSerializableOptionsTrait = $coreFiles->getCoreFileByEntityName(PHPFHIR_ENCODING_TRAIT_JSON_SERIALIZATION_OPTIONS);
$xmlSerializationOptionsTrait = $coreFiles->getCoreFileByEntityName(PHPFHIR_ENCODING_TRAIT_XML_SERIALIZATION_OPTIONS);
$xmlWriterClass = $coreFiles->getCoreFileByEntityName(PHPFHIR_ENCODING_CLASSNAME_XML_WRITER);
$unserializeConfig = $coreFiles->getCoreFileByEntityName(PHPFHIR_ENCODING_CLASSNAME_UNSERIALIZE_CONFIG);
$serializeConfig = $coreFiles->getCoreFileByEntityName(PHPFHIR_ENCODING_CLASSNAME_SERIALIZE_CONFIG);

$mockTypeFieldsTrait = $testCoreFiles->getCoreFileByEntityName(PHPFHIR_TEST_TRAIT_MOCK_TYPE_FIELDS);

$imports->addCoreFileImports(
    $resourceTypeInterface,
    $commentContainerInterface,
    $commentContainerTrait,
    $sourceXMLNSTrait,

    $typeValidationTrait,

    $jsonSerializableOptionsTrait,
    $xmlSerializationOptionsTrait,
    $xmlWriterClass,
    $unserializeConfig,
    $serializeConfig,

    $mockTypeFieldsTrait,
);

ob_start();
echo '<?php'; ?> declare(strict_types=1);

namespace <?php echo $coreFile->getFullyQualifiedNamespace(false); ?>;

<?php echo $config->getBasePHPFHIRCopyrightComment(true); ?>

<?php echo ImportUtils::compileImportStatements($imports); ?>

class <?php echo $coreFile; ?> implements <?php echo $resourceTypeInterface; ?>, <?php echo $commentContainerInterface; ?>

{
    use <?php echo $typeValidationTrait; ?>,
        <?php echo $jsonSerializableOptionsTrait; ?>,
        <?php echo $xmlSerializationOptionsTrait; ?>,
        <?php echo $commentContainerTrait; ?>,
        <?php echo $sourceXMLNSTrait; ?>,
        <?php echo $mockTypeFieldsTrait; ?>;

    private const _FHIR_VALIDATION_RULES = [];

    protected string $_name;
    protected array $_fields = [];

    private array $_valueXMLLocations = [];

    public function __construct(string $name,
                                array $fields = [],
                                array $validationRuleMap = [],
                                array $fhirComments = [])
    {
        $this->_name = $name;
        $this->_setFHIRComments($fhirComments);
        foreach($validationRuleMap as $field => $rules) {
            $this->_setFieldValidationRules($field, $rules);
        }
        $this->_processFields($fields);
    }

    public function _getFHIRTypeName(): string
    {
        return $this->_name;
    }

    public static function xmlUnserialize(\SimpleXMLElement|string $element,
                                          null|<?php echo $unserializeConfig; ?> $config = null,
                                          null|<?php echo $resourceTypeInterface; ?> $type = null): <?php echo $resourceTypeInterface; ?>

    {
        throw new \BadMethodCallException('xmlUnserialize not yet implemented');
    }

    public function xmlSerialize(null|<?php echo $xmlWriterClass; ?> $xw = null, null|<?php echo $serializeConfig; ?> $config = null): <?php echo $xmlWriterClass; ?>

    {
        if (null === $config) {
            $config = new <?php echo $serializeConfig; ?>();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode($this->_name, $this->_getSourceXMLNS());
        }

        $this->_xmlSerialize($xw, $config);

        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    public static function jsonUnserialize(string|\stdClass $json, null|<?php echo $unserializeConfig; ?> $config = null, null|<?php echo $resourceTypeInterface; ?> $type = null): <?php echo $resourceTypeInterface; ?>

    {
        throw new \BadMethodCallException('jsonUnserialize not yet implemented');
    }

    public function __toString(): string
    {
        return $this->_name;
    }
}
<?php return ob_get_clean();
