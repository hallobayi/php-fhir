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

use DCarbone\PHPFHIR\Enum\TestTypeEnum;

/** @var \DCarbone\PHPFHIR\Config $config */

ob_start();

echo '<?php'; ?>

namespace <?php echo $config->getFullyQualifiedTestsName(TestTypeEnum::BASE, false); ?>;

<?php echo $config->getBasePHPFHIRCopyrightComment(false); ?>


use <?php echo $config->getFullyQualifiedName(false, PHPFHIR_CLASSNAME_CONSTANTS); ?>;
use <?php echo $config->getFullyQualifiedName(false, PHPFHIR_CLASSNAME_FACTORY_CONFIG); ?>;
use <?php echo $config->getFullyQualifiedName(false, PHPFHIR_ENUM_FACTORY_CONFIG_KEY); ?>;
use PHPUnit\Framework\TestCase;

class <?php echo PHPFHIR_TEST_CLASSNAME_FACTORY_CONFIG; ?> extends TestCase
{
    public function testFactoryConfigEmpty(): void
    {
        $config = new <?php echo PHPFHIR_CLASSNAME_FACTORY_CONFIG; ?>();
        $this->assertCount(0, $config->getVersionsIterator());
    }

    public function testFactoryConfigVersionArrayMissingName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new <?php echo PHPFHIR_CLASSNAME_FACTORY_CONFIG; ?>([
            'versions' => [
                [
                    'class' => 'some class',
                ],
            ]
        ]);
    }

    public function testFactoryConfigVersionArrayMissingClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new <?php echo PHPFHIR_CLASSNAME_FACTORY_CONFIG; ?>([
            'versions' => [
                [
                    'name' => 'FHIRTEST',
                ],
            ]
        ]);
    }

    public function testFactoryConfigVersionArrayInvalidClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new <?php echo PHPFHIR_CLASSNAME_FACTORY_CONFIG; ?>([
            'versions' => [
                [
                    'name' => 'FHIRTEST',
                    'class' => '\\mygreatclass',
                ],
            ]
        ]);
    }
}
<?php
return ob_get_clean();
