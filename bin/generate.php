<?php
/**
 * Download and generation script for all major FHIR versions
 *
 * Copyright 2017 Pim Koeman (pim@dataground.com)
 * Copyright 2017-2019 Daniel Carbone (daniel.p.carbone@gmail.com)
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

namespace PHPFHIR;

// --- autoload setup

date_default_timezone_set('UTC');
require __DIR__ . '/../vendor/autoload.php';

// --- use statements

use DCarbone\PHPFHIR\Builder;
use DCarbone\PHPFHIR\Config;
use DCarbone\PHPFHIR\Definition;
use MyENA\DefaultANSILogger;

// ----- constants

const ENV_GENERATE_CONFIG_FILE = 'PHPFHIR_GENERATE_CONFIG_FILE';

const FLAG_HELP = '--help';
const FLAG_FORCE = '--force';
const FLAG_USE_EXISTING = '--useExisting';
const FLAG_CONFIG = '--config';
const FLAG_VERSIONS = '--versions';
const FLAG_LOG_LEVEL = '--logLevel';

// ----- Log Levels

const LOG_DEBUG = 'debug';
const LOG_INFO = 'info';
const LOG_WARN = 'warn';
const LOG_WARNING = 'warning';
const LOG_ERROR = 'error';

// ----- cli and config opts

$print_help = false;
$force_delete = false;
$config_env = getenv(ENV_GENERATE_CONFIG_FILE);
$config_arg = '';
$config_def = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config_file = null;
$versions_to_generate = null;
$use_existing = false;
$log_level = LOG_WARNING;

// ----- functions

/**
 * @param bool $return
 * @return string
 */
function missing_config_text($return)
{
    global $config_env, $config_arg, $config_def;
    $out = 'Unable to locate generate script configuration file.  I looked in the following locations:' . PHP_EOL;
    $out .= sprintf(
        '   - env var "%s": %s%s',
        ENV_GENERATE_CONFIG_FILE,
        (false === $config_env ? 'Not Defined' : $config_env),
        PHP_EOL
    );
    $out .= sprintf('   - "--config" flag: %s%s', ('' === $config_arg ? 'Not Defined' : $config_arg), PHP_EOL);
    $out .= sprintf('   - Default: %s%s', $config_def, PHP_EOL);
    $out .= PHP_EOL;
    $out .= 'Please do one of the following:' . PHP_EOL;
    $out .= sprintf('   - Define "%s" environment variable%s', ENV_GENERATE_CONFIG_FILE, PHP_EOL);
    $out .= '   - Pass "--config" flag with valid path to config file' . PHP_EOL;
    $out .= sprintf('   - Place "config.php" file in "%s"%s', $config_def, PHP_EOL);

    $exConfig = file_get_contents($config_def);

    $out .= <<<STRING

Below is an example config file:

{$exConfig}

STRING;

    if ($return) {
        return $out;
    }

    echo $out;
    exit(1);
}


function exit_with_help($err = false)
{
    global $config_def;
    $env_var = ENV_GENERATE_CONFIG_FILE;
    $out = sprintf(<<<STRING

PHP-FHIR: Tools for creating PHP classes from the HL7 FHIR Specification

- Links: 
    Source:         https://github.com/dcarbone/php-fhir
    Generated:      https://github.com/dcarbone/php-fhir-generated
    FHIR:           http://hl7.org/fhir

- Flags:
    --help:         Print this help text 
                        ex: ./bin/generate.sh --help
    --force:        Forcibly delete all pre-existing FHIR schema files and output files without being prompted 
                        ex: ./bin/generate.sh --force
    --useExisting:  Do no prompt for any cleanup tasks.  Mutually exclusive with --force
                        ex: ./bin/generate.sh --useExisting
    --config:       Specify location of config [default: {$config_def}]
                        ex: ./bin/generate.sh --config path/to/file
    --versions:     Comma-separated list of specific versions to parse from config
                        ex: ./bin/generate.sh --versions DSTU1,DSTU2
    --logLevel:     Level of verbosity during generation
                        ex: ./bin/generate.sh --logLevel warning

- Configuration:
    There are 3 possible ways to define a configuration file for this script to use:
        1. Define env var {$env_var}
        2. Pass "--config" flag at run time
        3. Place "config.php" in dir {$config_def}


STRING
    );

    echo $out;
    if ($err) {
        exit(1);
    }
    exit(0);
}

/**
 * TODO: Figure out what to do with Windows...
 *
 * @param string $q
 *
 * @return bool
 */
function ask($q)
{
    global $ins, $null;
    echo "{$q} [enter \"yes\" or \"no\"]: ";
    while (0 !== stream_select($ins, $null, $null, null)) {
        foreach ($ins as $in) {
            $resp = stream_get_line($in, 25, "\n");
            if (is_string($resp)) {
                return substr(strtolower($resp), 0, 1) === 'y';
            }
            return false;
        }
    }
    // some kind of error checking?
    return false;
}

/**
 * @param string $dir
 */
function nuke_dir($dir)
{
    echo "Executing \"rm -rf {$dir}\" ...\n";
    shell_exec('rm -rf ' . $dir);
    sleep(1);
    if (file_exists($dir)) {
        echo "Unable to delete dir {$dir}\n";
        exit(1);
    }
    echo "Done.\n";
}

/**
 * @param string $dir
 * @return bool
 */
function is_dir_empty($dir)
{
    return 0 === iterator_count(new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS));
}


// ----- parameter parsing

if ($argc > 1) {
    for ($i = 1; $i < $argc; $i++) {
        $arg = trim($argv[$i]);
        $found_equal = false; // TODO: super hacky...
        if (($i + 1) >= $argc) {
            $next = null;
        } else {
            $next = trim($argv[$i + 1]);
        }
        if (false !== strpos($arg, '=')) {
            list($arg, $next) = explode('=', $arg, 2);
            $found_equal = true;
        }
        switch ($arg) {
            case FLAG_HELP:
                $print_help = true;
                break;

            case FLAG_FORCE:
                $force_delete = true;
                break;

            case FLAG_USE_EXISTING:
                $use_existing = true;
                break;

            case FLAG_CONFIG:
                $config_arg = trim($next);
                if (!$found_equal) {
                    $i++;
                }
                break;

            case FLAG_LOG_LEVEL:
                $log_level = trim($next);
                if (!$found_equal) {
                    $i++;
                }
                break;

            case FLAG_VERSIONS:
                $versions_to_generate = array_map('trim', explode(',', $next));
                if (!$found_equal) {
                    $i++;
                }
                break;

            default:
                echo "Unknown argument \"{$arg}\" passed at position {$i}\n";
                exit_with_help(true);
        }
    }
}

if ($use_existing && $force_delete) {
    echo sprintf(
        "Flags %s and %s are mutually exclusive, please specify one or the other.\n",
        FLAG_FORCE,
        FLAG_USE_EXISTING
    );
    exit_with_help(true);
}

// try to determine which config file to use...
if ('' !== $config_arg) {
    $config_file = $config_arg;
} elseif (false !== $config_env) {
    $config_file = $config_env;
} else {
    $config_file = $config_def;
}

if ($print_help) {
    exit_with_help(); // calls exit(0); at end
}

if (!file_exists($config_file)) {
    missing_config_text(false);
}

if (!is_readable($config_file)) {
    echo "Specified config file \"{$config_file}\" is not readable by this process, please check permissions and try again\n";
    exit(1);
}

// logger setup
if (class_exists('\\MyENA\\DefaultANSILogger')) {
    $logger = new DefaultANSILogger();
    $log_level = strtolower($log_level);
    if (LOG_WARN === $log_level) {
        $log_level = LOG_WARNING;
    }
    $logger->setLogLevel($log_level);
} else {
    $logger = null;
}

// build configuration
$config = new Config(require $config_file, $logger);

// test provided versions are defined
if (null === $versions_to_generate) {
    $versions_to_generate = $config->listVersions();
}

// test specified versions
foreach ($versions_to_generate as $vg) {
    if (!$config->hasVersion($vg)) {
        echo sprintf(
            "Version \"%s\" not found in config.  Available: %s\n\n",
            $vg,
            implode(', ', $config->listVersions())
        );
        exit(1);
    }
}

$ins = [STDIN];
$null = null;

// try to clean up working dir
$dir = $config->getClassesPath() . DIRECTORY_SEPARATOR . 'HL7';
if (is_dir($dir)) {
    if (!$use_existing && ($force_delete ||
            ask("Work Directory \"{$dir}\" already exists.\nWould you like to purge its current contents prior to generation?"))
    ) {
        nuke_dir($dir);
    } else {
        echo "Continuing without work directory cleanup\n";
    }
}

echo sprintf(
    "\nGenerating classes for versions: %s\n\n",
    implode(', ', $versions_to_generate)
);

foreach ($versions_to_generate as $version) {
    $buildConfig = new Config\VersionConfig($config, $config->getVersion($version));

    $url = $buildConfig->getUrl();

    $namespace = $buildConfig->getNamespace();
    $version = trim($version);
    $schema_dir = $config->getSchemaPath() . DIRECTORY_SEPARATOR . $version;

    // Download zip files
    $zip_file_name = $config->getSchemaPath() . DIRECTORY_SEPARATOR . $version . '.zip';
    $zip_exists = file_exists($zip_file_name);

    $download = $unzip = true;

    if ($zip_exists) {
        if (!$use_existing && ($force_delete ||
                ask("ZIP \"{$zip_file_name}\" already exists.\nWould you like to re-download from \"{$url}\"?"))
        ) {
            echo "Deleting {$zip_file_name} ...\n";
            unlink($zip_file_name);
            if (file_exists($zip_file_name)) {
                echo "Unable to delete file {$zip_file_name}\n";
                exit(1);
            }
            echo "Deleted.\n";
        } else {
            echo "Using existing local copy\n";
            $download = false;
        }
    }

    if ($download) {
        echo 'Downloading ' . $version . ' from ' . $url . PHP_EOL;
        // Download/extract ZIP file
        if (!copy($url, $zip_file_name)) {
            echo "Unable to download.\n";
            exit(1);
        }
    }

    if (is_dir($schema_dir)) {
        if (is_dir_empty($schema_dir)) {
            // TODO: is this necessary...?
            echo "Schema dir \"{$schema_dir}\" is empty, will remove and re-create\n";
            nuke_dir($schema_dir);
            if (!mkdir($schema_dir, 0755, true)) {
                echo "Unable to create directory \"{$schema_dir}\. Exiting\n";
                exit(1);
            }
        } elseif (!$download) {
            echo "Did not download new zip and schema dir \"{$schema_dir}\" already exists, using...\n";
            $unzip = false;
        } elseif (!$use_existing) {
            if ($force_delete || ask("Schema dir \"{$schema_dir}\" already exists, ok to delete?")) {
                nuke_dir($schema_dir);
                if (!mkdir($schema_dir, 0755, true)) {
                    echo "Unable to create directory \"{$schema_dir}\. Exiting\n";
                    exit(1);
                }
            } else {
                echo "Exiting\n";
                exit(0);
            }
        }
    }

    if ($unzip) {
        if (!class_exists('\\ZipArchive', true)) {
            echo "ext-zip not found, cannot unzip.\n";
            exit(1);
        }
        $zip = new \ZipArchive;

        if (true !== ($res = $zip->open($schema_dir . '.zip'))) {
            echo "Unable to open file {$schema_dir}.zip.  ZipArchive err: {$res}\n";
            exit(1);
        }

        // Extract Zip
        $zip->extractTo($schema_dir);
        $zip->close();
    }

    if (is_dir($config->getClassesPath())) {
        if (is_dir_empty($config->getClassesPath())) {
            echo "Output directory \"{$config->getClassesPath()}\" already exists, but is empty.  Will use.\n";
        } elseif ($force_delete) {
            echo "Output directory \"{$config->getClassesPath()}\" already exists, deleting...\n";
            nuke_dir($config->getClassesPath());
            if (!mkdir($config->getClassesPath(), 0755, true)) {
                echo "Unable to create directory \"{$config->getClassesPath()}\". Exiting.\n";
                exit(1);
            }
        } else {
            echo "Output Directory \"{$config->getClassesPath()}\" already exists.\n";
            if (!$use_existing) {
                if (ask('Would you like to delete the directory?')) {
                    nuke_dir($config->getClassesPath());
                    if (!mkdir($config->getClassesPath(), 0755, true)) {
                        echo "Unable to create directory \"{$config->getClassesPath()}\".  Exiting.\n";
                        exit(1);
                    }
                } else {
                    echo "Exiting.\n";
                    exit(0);
                }
            }
        }
    }

    echo sprintf(
        'Generating "%s" into %s%s%s%s',
        $version,
        $config->getClassesPath(),
        DIRECTORY_SEPARATOR,
        str_replace('\\', DIRECTORY_SEPARATOR, $namespace),
        PHP_EOL
    );

    $definition = new Definition($buildConfig);
    $definition->buildDefinition();

    $builder = new Builder($buildConfig, $definition);
    $builder->build();
}

echo PHP_EOL . 'Generation completed' . PHP_EOL;