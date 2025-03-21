<?php declare(strict_types=1);

/*
 * Copyright 2018-2025 Daniel Carbone (daniel.p.carbone@gmail.com)
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

use DCarbone\PHPFHIR\Utilities\FileUtils;

/** @var \DCarbone\PHPFHIR\Config $config */
/** @var \DCarbone\PHPFHIR\CoreFile $coreFile */

$coreFiles = $config->getCoreFiles();

ob_start();
echo '<?php ';?>declare(strict_types=1);

namespace <?php echo $coreFile->getFullyQualifiedNamespace(false); ?>;

<?php echo $config->getBasePHPFHIRCopyrightComment(true); ?>

abstract class <?php echo $coreFile; ?>

{
    private const _ROOT_NAMESPACE = '<?php echo $config->getFullyQualifiedName(false); ?>\\';

    private const _VERSION_AUTOLOADER_MAP = [
<?php foreach($config->getVersionsIterator() as $i => $version): ?>
        <?php echo $i; ?> => [
            '<?php echo $version->getFullyQualifiedName(false); ?>\\',
            '<?php echo $version->getFullyQualifiedName(false, PHPFHIR_CLASSNAME_AUTOLOADER); ?>',
            <?php echo FileUtils::buildAutoloaderRelativeFilepath(
                $config->getFullyQualifiedName(false),
                $version->getFullyQualifiedName(false, PHPFHIR_CLASSNAME_AUTOLOADER),
            ); ?>,
        ],
<?php endforeach; ?>
    ];

    private const _CORE_CLASS_MAP = [
        // core types
<?php foreach($coreFiles->getIterator() as $cf): if ($cf->isAutoloader() || $cf->isTest()) { continue; } ?>
        '<?php echo $cf->getFullyQualifiedName(false); ?>' => <?php echo FileUtils::buildAutoloaderRelativeFilepath(
        $config->getFullyQualifiedName(false),
        $cf->getFullyQualifiedName(false),
    ); ?>,
<?php endforeach; ?>    ];

    private static bool $_registered = false;

    private static array $_versionRegistered = [
<?php foreach($config->getVersionsIterator() as $i => $version) : ?>
        <?php echo $i; ?> => false,
<?php endforeach; ?>
    ];

    public static function register(): bool
    {
        if (!self::$_registered) {
            self::$_registered = spl_autoload_register(__CLASS__ . '::loadClass');
        }
        return self::$_registered;
    }

    public static function unregister(): bool
    {
        if (self::$_registered) {
            if (spl_autoload_unregister(__CLASS__ . '::loadClass')) {
                self::$_registered = false;
                return true;
            }
        }
        return false;
    }

    public static function loadClass(string $class): null|bool
    {
        if (isset(self::_CORE_CLASS_MAP[$class])) {
            return (bool)require self::_CORE_CLASS_MAP[$class];
        } else if (!str_starts_with($class, self::_ROOT_NAMESPACE)) {
            return null;
        }<?php foreach($config->getVersionsIterator() as $i => $version): ?> else if (str_starts_with($class, self::_VERSION_AUTOLOADER_MAP[<?php echo $i; ?>][0])) {
            if (self::$_versionRegistered[<?php echo $i; ?>]) {
                return null;
            }
            require self::_VERSION_AUTOLOADER_MAP[<?php echo $i; ?>][2];
            <?php echo $version->getFullyQualifiedName(true, PHPFHIR_CLASSNAME_AUTOLOADER); ?>::register();
            self::$_versionRegistered[<?php echo $i; ?>] = true;
            if ($class !== self::_VERSION_AUTOLOADER_MAP[<?php echo $i; ?>][1]) {
                return <?php echo $version->getFullyQualifiedName(true, PHPFHIR_CLASSNAME_AUTOLOADER); ?>::loadClass($class);
            } else {
                return true;
            }
        }<?php endforeach; ?> else {
            return null;
        }
    }
}

<?php echo PHPFHIR_CLASSNAME_AUTOLOADER; ?>::register();
<?php return ob_get_clean();
