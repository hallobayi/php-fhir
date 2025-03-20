<?php declare(strict_types=1);

/*
 * Copyright 2016-2024 Daniel Carbone (daniel.p.carbone@gmail.com)
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

// conf defaults
define('PHPFHIR_ROOT_DIR', realpath(dirname(__DIR__)));
define('PHPFHIR_BIN_DIR', realpath(PHPFHIR_ROOT_DIR . DIRECTORY_SEPARATOR . 'bin'));
define('PHPFHIR_DEFAULT_OUTPUT_DIR', realpath(PHPFHIR_ROOT_DIR . DIRECTORY_SEPARATOR . 'output'));
define('PHPFHIR_TEMPLATE_DIR', realpath(PHPFHIR_ROOT_DIR . DIRECTORY_SEPARATOR . 'template'));

const PHPFHIR_NAMESPACE_SEPARATOR = '\\';

// some defaults

const PHPFHIR_DEFAULT_LIBXML_OPT_MASK = 'LIBXML_NONET | LIBXML_BIGLINES | LIBXML_PARSEHUGE | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOXMLDECL';
define('PHPFHIR_DEFAULT_LIBXML_OPTS', eval('return ' . PHPFHIR_DEFAULT_LIBXML_OPT_MASK . ';'));
const PHPFHIR_DEFAULT_JSON_DECODE_OPT_MASK = 'JSON_BIGINT_AS_STRING';
define('PHPFHIR_DEFAULT_JSON_DECODE_OPTS', eval('return ' . PHPFHIR_DEFAULT_JSON_DECODE_OPT_MASK . ';'));

// namespace segments
const PHPFHIR_NAMESPACE_VERSIONS = 'Versions';
const PHPFHIR_NAMESPACE_TYPES = 'Types';
const PHPFHIR_NAMESPACE_TESTS = 'Tests';

// type suffixes
const PHPFHIR_PRIMITIVE_SUFFIX = '-primitive';
const PHPFHIR_LIST_SUFFIX = '-list';

// html property
const PHPFHIR_XHTML_DIV = 'xhtml:div';

// raw type
const PHPFHIR_XHTML_TYPE_NAME = 'XHTML';

// FHIR XML NS
const PHPFHIR_FHIR_XMLNS = 'https://hl7.org/fhir';

// XSDs
const PHPFHIR_SKIP_XML_XSD = 'xml.xsd';
const PHPFHIR_SKIP_XHTML_XSD = 'fhir-xhtml.xsd';
const PHPFHIR_SKIP_TOMBSTONE_XSD = 'tombstone.xsd';
const PHPFHIR_SKIP_ATOM_XSD_PREFIX = 'fhir-atom';
const PHPFHIR_SKIP_FHIR_XSD_PREFIX = 'fhir-';

// Properties
const PHPFHIR_UNLIMITED = -1;
const PHPFHIR_VALUE_PROPERTY_NAME = 'value';

// Rendering
const PHPFHIR_DOCBLOC_MAX_LENGTH = 80;
const PHPFHIR_NAMESPACE_TRIM_CUTSET = " \t\n\r\0\x0b\\/";

// Core interfaces, traits, and classes
const PHPFHIR_TEMPLATE_CORE_DIR = PHPFHIR_TEMPLATE_DIR . DIRECTORY_SEPARATOR . 'core';

// Version interfaces, traits, and enums
const PHHPFHIR_TEMPLATE_VERSIONS_DIR = PHPFHIR_TEMPLATE_DIR . DIRECTORY_SEPARATOR . 'versions';
const PHPFHIR_TEMPLATE_VERSIONS_CORE_DIR = PHHPFHIR_TEMPLATE_VERSIONS_DIR . DIRECTORY_SEPARATOR . 'core';

// Version type rendering
const PHPFHIR_TEMPLATE_VERSION_TYPES_DIR = PHHPFHIR_TEMPLATE_VERSIONS_DIR . DIRECTORY_SEPARATOR . 'types';
const PHPFHIR_TEMPLATE_VERSION_TYPES_PROPERTIES_DIR = PHPFHIR_TEMPLATE_VERSION_TYPES_DIR . DIRECTORY_SEPARATOR . 'properties';
const PHPFHIR_TEMPLATE_VERSION_TYPES_METHODS_DIR = PHPFHIR_TEMPLATE_VERSION_TYPES_DIR . DIRECTORY_SEPARATOR . 'methods';
const PHPFHIR_TEMPLATE_VERSION_TYPES_SERIALIZATION_DIR = PHPFHIR_TEMPLATE_VERSION_TYPES_DIR . DIRECTORY_SEPARATOR . 'serialization';

// Test template paths
const PHPFHIR_TEMPLATE_TESTS_DIR = PHPFHIR_TEMPLATE_DIR . DIRECTORY_SEPARATOR . 'tests';
const PHPFHIR_TEMPLATE_TESTS_CORE_DIR = PHPFHIR_TEMPLATE_TESTS_DIR . DIRECTORY_SEPARATOR . 'core';
const PHPFHIR_TEMPLATE_TESTS_VERSIONS_CORE_DIR = PHPFHIR_TEMPLATE_TESTS_DIR . DIRECTORY_SEPARATOR . 'versions' . DIRECTORY_SEPARATOR . 'core';
const PHPFHIR_TEMPLATE_TESTS_VERSIONS_TYPES_DIR = PHPFHIR_TEMPLATE_TESTS_DIR . DIRECTORY_SEPARATOR . 'versions' . DIRECTORY_SEPARATOR . 'types';

// Core class names
const PHPFHIR_CLASSNAME_AUTOLOADER = 'Autoloader';
const PHPFHIR_CLASSNAME_VERSION_CONFIG = 'VersionConfig';
const PHPFHIR_CLASSNAME_CONSTANTS = 'Constants';
const PHPFHIR_CLASSNAME_FHIR_VERSION = 'FHIRVersion';

// Core interface names
const PHPFHIR_INTERFACE_VERSION = 'VersionInterface';
const PHPFHIR_INTERFACE_VERSION_CONFIG = 'VersionConfigInterface';
const PHPFHIR_INTERFACE_VERSION_TYPE_MAP = 'VersionTypeMapInterface';

// Core exceptions
const PHPFHIR_EXCEPTION_CLIENT_ABSTRACT_CLIENT = 'AbstractClientException';
const PHPFHIR_EXCEPTION_CLIENT_ERROR = 'ClientErrorException';
const PHPFHIR_EXCEPTION_CLIENT_UNEXPECTED_RESPONSE_CODE = 'UnexpectedResponseCodeException';

// Core types entities
const PHPFHIR_TYPES_INTERFACE_TYPE = 'TypeInterface';
const PHPFHIR_TYPES_INTERFACE_PRIMITIVE_TYPE = 'PrimitiveTypeInterface';
const PHPFHIR_TYPES_INTERFACE_ELEMENT_TYPE = 'ElementTypeInterface';
const PHPFHIR_TYPES_INTERFACE_PRIMITIVE_CONTAINER_TYPE = 'PrimitiveContainerTypeInterface';
const PHPFHIR_TYPES_INTERFACE_VALUE_CONTAINER_TYPE = 'ValueContainerTypeInterface';
const PHPFHIR_TYPES_INTERFACE_RESOURCE_ID_TYPE = 'ResourceIDTypeInterface';
const PHPFHIR_TYPES_INTERFACE_RESOURCE_TYPE = 'ResourceTypeInterface';
const PHPFHIR_TYPES_INTERFACE_CONTAINED_TYPE = 'ContainedTypeInterface';
const PHPFHIR_TYPES_INTERFACE_RESOURCE_CONTAINER_TYPE = 'ResourceContainerTypeInterface';
const PHPFHIR_TYPES_INTERFACE_COMMENT_CONTAINER = 'CommentContainerInterface';
const PHPFHIR_TYPES_TRAIT_COMMENT_CONTAINER = 'CommentContainerTrait';
const PHPFHIR_TYPES_TRAIT_VALUE_CONTAINER = 'ValueContainerTrait';
const PHPFHIR_TYPES_TRAIT_SOURCE_XMLNS = 'SourceXMLNamespaceTrait';

// Core encoding entities
const PHPFHIR_ENCODING_CLASSNAME_XML_WRITER = 'XMLWriter';
const PHPFHIR_ENCODING_ENUM_VALUE_XML_LOCATION = 'ValueXMLLocationEnum';
const PHPFHIR_ENCODING_CLASSNAME_SERIALIZE_CONFIG = 'SerializeConfig';
const PHPFHIR_ENCODING_CLASSNAME_UNSERIALIZE_CONFIG = 'UnserializeConfig';
const PHPFHIR_ENCODING_CLASSNAME_RESOURCE_PARSER = 'ResourceParser';
const PHPFHIR_ENCODING_TRAIT_JSON_SERIALIZATION_OPTIONS = 'JSONSerializationOptionsTrait';
const PHPFHIR_ENCODING_TRAIT_XML_SERIALIZATION_OPTIONS = 'XMLSerializationOptionsTrait';
const PHPFHIR_ENCODING_ENUM_SERIALIZE_FORMAT = 'SerializeFormatEnum';

// Core client entities
const PHPFHIR_CLIENT_INTERFACE_CLIENT = 'ClientInterface';
const PHPFHIR_CLIENT_CLASSNAME_CLIENT = 'Client';
const PHPFHIR_CLIENT_CLASSNAME_CONFIG = 'Config';
const PHPFHIR_CLIENT_CLASSNAME_REQUEST = 'Request';
const PHPFHIR_CLIENT_CLASSNAME_RESPONSE = 'Response';
const PHPFHIR_CLIENT_CLASSNAME_RESPONSE_HEADERS = 'ResponseHeaders';
const PHPFHIR_CLIENT_ENUM_HTTP_METHOD = 'HTTPMethodEnum';
const PHPFHIR_CLIENT_ENUM_SORT_DIRECTION = 'SortDirectionEnum';

// Version core entities
const PHPFHIR_VERSION_CLASSNAME_VERSION = 'Version';
const PHPFHIR_VERSION_CLASSNAME_VERSION_CONSTANTS = 'VersionConstants';
const PHPFHIR_VERSION_CLASSNAME_VERSION_TYPE_MAP = 'VersionTypeMap';
const PHPFHIR_VERSION_CLASSNAME_VERSION_CLIENT = 'VersionClient';
const PHPFHIR_VERSION_INTERFACE_VERSION_CONTAINED_TYPE = 'VersionContainedTypeInterface';
const PHPFHIR_VERSION_ENUM_VERSION_RESOURCE_TYPE = 'VersionResourceTypeEnum';
const PHPFHIR_VERSION_INTERFACE_RESOURCE_TYPE = 'VersionResourceTypeInterface';

// Validation entities
const PHPFHIR_VALIDATION_CLASSNAME_VALIDATOR = 'Validator';
const PHPFHIR_VALIDATION_INTERFACE_RULE = 'RuleInterface';
const PHPFHIR_VALIDATION_TRAIT_TYPE_VALIDATIONS = 'TypeValidationsTrait';

// Base validation rules
const PHPFHIR_VALIDATION_RULE_CLASSNAME_VALUE_ONE_OF = 'ValueOneOfRule';
const PHPFHIR_VALIDATION_RULE_CLASSNAME_VALUE_MIN_LENGTH = 'ValueMinLengthRule';
const PHPFHIR_VALIDATION_RULE_CLASSNAME_VALUE_MAX_LENGTH = 'ValueMaxLengthRule';
const PHPFHIR_VALIDATION_RULE_CLASSNAME_VALUE_PATTERN_MATCH = 'ValuePatternMatchRule';
const PHPFHIR_VALIDATION_RULE_CLASSNAME_MIN_OCCURS = 'MinOccursRule';
const PHPFHIR_VALIDATION_RULE_CLASSNAME_MAX_OCCURS = 'MaxOccursRule';


// Static test type, namespace, and class values.
const PHPFHIR_TEST_CLASSNAME_CONSTANTS = PHPFHIR_CLASSNAME_CONSTANTS . 'Test';
const PHPFHIR_TEST_ENCODING_CLASSNAME_UNSERIALIZE_CONFIG = PHPFHIR_ENCODING_CLASSNAME_UNSERIALIZE_CONFIG . 'Test';
const PHPFHIR_TEST_ENCODING_CLASSNAME_SERIALIZE_CONFIG = PHPFHIR_ENCODING_CLASSNAME_SERIALIZE_CONFIG . 'Test';
const PHPFHIR_TEST_ENCODING_CLASSSNAME_XML_WRITER = PHPFHIR_ENCODING_CLASSNAME_XML_WRITER . 'Test';
const PHPFHIR_TEST_CLIENT_CLASSNAME_CONFIG = PHPFHIR_CLIENT_CLASSNAME_CONFIG . 'Test';
const PHPFHIR_TEST_CLIENT_CLASSNAME_CLIENT = PHPFHIR_CLIENT_CLASSNAME_CLIENT . 'Test';

const PHPFHIR_TEST_CLASSNAME_ABSTRACT_MOCK_TYPE = 'AbstractMockType';
const PHPFHIR_TEST_TRAIT_MOCK_TYPE_FIELDS = 'MockTypeFieldsTrait';
const PHPFHIR_TEST_TRAIT_MOCK_JSON_FIELD_EXTRACTOR = 'MockJSONFieldExtractorTrait';
const PHPFHIR_TEST_CLASSNAME_MOCK_STRING_PRIMITIVE_TYPE = 'MockStringPrimitiveType';
const PHPFHIR_TEST_CLASSNAME_MOCK_ELEMENT_TYPE = 'MockElementType';
const PHPFHIR_TEST_CLASSNAME_MOCK_PRIMITIVE_CONTAINER_TYPE = 'MockPrimitiveContainerType';
const PHPFHIR_TEST_CLASSNAME_MOCK_RESOURCE_ID_TYPE = 'MockResourceIDType';
const PHPFHIR_TEST_CLASSNAME_MOCK_RESOURCE_TYPE = 'MockResourceType';
const PHPFHIR_TEST_CLASSNAME_MOCK_VERSION = 'MockVersion';
const PHPFHIR_TEST_CLASSNAME_MOCK_VERSION_TYPE_MAP = 'MockVersionTypeMap';
const PHPFHIR_TEST_CLASSNAME_MOCK_VERSION_CONFIG = 'MockVersionConfig';

// Test constant names
const PHPFHIR_TEST_CONSTANT_SERVER_ADDR = 'PHPFHIR_TEST_SERVER_ADDR';
const PHPFHIR_TEST_CONSTANT_RESOURCE_DOWNLOAD_DIR = 'PHPFHIR_TEST_RESOURCE_DOWNLOAD_DIR';

// Date & time formats
const PHPFHIR_DATE_FORMAT_YEAR = 'Y';
const PHPFHIR_DATE_FORMAT_YEAR_MONTH = 'Y-m';
const PHPFHIR_DATE_FORMAT_YEAR_MONTH_DAY = 'Y-m-d';
const PHPFHIR_DATE_FORMAT_YEAR_MONTH_DAY_TIME = 'Y-m-d\\TH:i:s\\.uP';
const PHPFHIR_DATE_FORMAT_INSTANT = 'Y-m-d\\TH:i:s\\.uP';
const PHPFHIR_TIME_FORMAT = 'H:i:s';

// misc
const PHPFHIR_JSON_FIELD_RESOURCE_TYPE = 'resourceType';