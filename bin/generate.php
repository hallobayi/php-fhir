<?php declare(strict_types=1);

/**
 * Download and generation script for all major FHIR versions
 *
 * Copyright 2017 Pim Koeman (pim@dataground.com)
 * Copyright 2017-2024 Daniel Carbone (daniel.p.carbone@gmail.com)
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

namespace PHPFHIRCLI;

date_default_timezone_set('UTC');

// --- autoload setup
const AUTOLOAD_CLASS_FILEPATH = __DIR__ . '/../vendor/autoload.php';

$autoloadClasspath = realpath(AUTOLOAD_CLASS_FILEPATH);

// ensure composer autoload class exists.
if (!$autoloadClasspath) {
    echo 'Unable to locate composer autoload file expected at path: ' . AUTOLOAD_CLASS_FILEPATH . PHP_EOL . PHP_EOL;
    echo "Please run \"composer install\" from the root of the project directory" . PHP_EOL . PHP_EOL;
    exit(1);
}

require $autoloadClasspath;

// ----- use statements

use DCarbone\PHPFHIR\Builder;
use DCarbone\PHPFHIR\Config;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

// ----- constants

const ENV_GENERATE_CONFIG_FILE = 'PHPFHIR_GENERATE_CONFIG_FILE';

const FLAG_HELP = '--help';
const FLAG_FORCE_DELETE = '--forceDelete';
const FLAG_USE_EXISTING = '--useExisting';
const FLAG_CONFIG = '--config';
const FLAG_ONLY_CORE = '--onlyCore';
const FLAG_ONLY_LIBRARY = '--onlyLibrary';
const FLAG_VERSIONS = '--versions';
const FLAG_USERAGENT = '--userAgent';
const FLAG_LOG_LEVEL = '--logLevel';

// ----- cli and config opts

$print_help = false;
$force_delete = false;
$config_location_def = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$config_location_env = getenv(ENV_GENERATE_CONFIG_FILE);
$config_location_arg = '';
$config_file = null;
$only_core = false;
$only_library = false;
$versions_to_generate = null;
$use_existing = false;
$log_level = LogLevel::WARNING;
$user_agent_def = 'Mozilla/5.0 (Android 4.4; Mobile; rv:41.0) Gecko/41.0 Firefox/41.0';
$user_agent = $user_agent_def;

// ----- functions

/**
 * @return string
 */
function missing_config_text(): string
{
    global $config_location_env, $config_location_arg, $config_location_def;
    $out = 'Unable to locate generate script configuration file.  I looked in the following locations:' . PHP_EOL;
    $out .= sprintf(
        '   - env var "%s": %s%s',
        ENV_GENERATE_CONFIG_FILE,
        (false === $config_location_env ? 'Not Defined' : $config_location_env),
        PHP_EOL
    );
    $out .= sprintf(
        '   - "--config" flag: %s%s',
        ('' === $config_location_arg ? 'Not Defined' : $config_location_arg),
        PHP_EOL
    );
    $out .= sprintf('   - Default: %s%s', $config_location_def, PHP_EOL);
    $out .= PHP_EOL;
    $out .= 'Please do one of the following:' . PHP_EOL;
    $out .= sprintf('   - Define "%s" environment variable%s', ENV_GENERATE_CONFIG_FILE, PHP_EOL);
    $out .= '   - Pass "--config" flag with valid path to config file' . PHP_EOL;
    $out .= sprintf('   - Place "config.php" file in "%s"%s', $config_location_def, PHP_EOL);

    $exConfig = file_get_contents($config_location_def);

    $out .= <<<STRING

Below is an example config file:

{$exConfig}

STRING;

    return $out;
}

/**
 * @return string
 */
function help_text(): string
{
    global $config_location_def, $user_agent_def;

    $config_env_var = ENV_GENERATE_CONFIG_FILE;
    $out = <<<STRING

PHP-FHIR: Tools for creating PHP classes from the HL7 FHIR Specification

Copyright 2016-2024 Daniel Carbone (daniel.p.carbone@gmail.com)

- Links: 
    Source:         https://github.com/dcarbone/php-fhir
    Generated:      https://github.com/dcarbone/php-fhir-generated
    FHIR:           https://hl7.org/fhir

- Flags:
    --help          Print this help text 
                        ex: ./bin/generate.sh --help
    --forceDelete   Forcibly delete all pre-existing FHIR schema files and output files without being prompted 
                        ex: ./bin/generate.sh --forceDelete
    --useExisting   Do no prompt for any cleanup tasks.  Mutually exclusive with --forceDelete
                        ex: ./bin/generate.sh --useExisting
    --onlyCore      Only gnerate Core classes.  Mutually exclusive with --onlyLibrary and --onlyTests
                        ex: ./bin/generate.sh --onlyCore
    --onlyLibrary   Only generate Library classes.  Mutually exclusive with --onlyTests
                        ex: ./bin/generate.sh --onlyLibrary
    --config        Specify location of config
                        default: {$config_location_def}
                        ex: ./bin/generate.sh --config path/to/file
    --versions      Comma-separated list of specific versions to parse from config
                        ex: ./bin/generate.sh --versions STU3,R4
    --userAgent     User-Agent string to use when downloading FHIR schema files
                        default: "{$user_agent_def}"
                        ex: ./bin/generate.sh --userAgent "your custom user agent string"
    --logLevel      Level of verbosity during generation
                        ex: ./bin/generate.sh --logLevel warning

- Configuration:
    There are 3 possible ways to define a configuration file for this script to use:
        1. Define env var {$config_env_var}
        2. Pass "--config" flag at run time
        3. Place "config.php" in dir {$config_location_def}


STRING;

    return $out;
}

/**
 * TODO: Figure out what to do with Windows...
 *
 * @param string $q
 * @return bool
 */
function ask(string $q): bool
{
    global $ins, $null;
    echo "{$q} [enter \"yes\" or \"no\"]: ";
    while (0 !== stream_select($ins, $null, $null, null)) {
        foreach ($ins as $in) {
            $resp = stream_get_line($in, 8, PHP_EOL);
            if (is_string($resp)) {
                return str_starts_with(strtolower($resp), 'y');
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
function nuke_dir(string $dir): void
{
    echo "Executing \"rm -rf {$dir}\" ..." . PHP_EOL;
    shell_exec('rm -rf ' . $dir);
    sleep(1);
    if (file_exists($dir)) {
        echo "Unable to delete dir {$dir}" . PHP_EOL;
        exit(1);
    }
    echo "Done." . PHP_EOL;
}

/**
 * @param string $dir
 * @return bool
 */
function is_dir_empty(string $dir): bool
{
    $res = glob($dir, GLOB_NOSORT);
    foreach ($res as $r) {
        if (str_starts_with($r, '.')) {
            continue;
        }
        return false;
    }
    return true;
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
        if (str_contains($arg, '=')) {
            [$arg, $next] = explode('=', $arg, 2);
            $found_equal = true;
        }
        switch ($arg) {
            case '?';
            case '-h':
            case '-help':
            case 'help':
            case FLAG_HELP:
                $print_help = true;
                break;

            case FLAG_FORCE_DELETE:
                $force_delete = true;
                break;

            case FLAG_USE_EXISTING:
                $use_existing = true;
                break;

            case FLAG_CONFIG:
                $config_location_arg = trim($next);
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

            case FLAG_ONLY_CORE:
                $only_core = true;
                break;

            case FLAG_ONLY_LIBRARY:
                $only_library = true;
                break;

            case FLAG_USERAGENT:
                $user_agent = trim($next);
                if (!$found_equal) {
                    $i++;
                }
                break;

            default:
                echo "Unknown argument \"{$arg}\" passed at position {$i}" . PHP_EOL;
                echo help_text();
                exit(1);
        }
    }
}

// check if they want the help text before doing anything else.
if ($print_help) {
    echo help_text();
    exit(0);
}

// try to determine which config file to use...
if ('' !== $config_location_arg) {
    $config_file = $config_location_arg;
} elseif (false !== $config_location_env) {
    $config_file = $config_location_env;
} else {
    $config_file = $config_location_def;
}

if (!file_exists($config_file)) {
    echo missing_config_text();
    exit(1);
}

if (!is_readable($config_file)) {
    echo "Specified config file \"{$config_file}\" is not readable by this process, please check permissions and try again" . PHP_EOL;
    exit(1);
}

// ensure no mutually exclusive flag conflicts exist...

if ($use_existing && $force_delete) {
    echo help_text();
    echo PHP_EOL;
    echo "Flags " . FLAG_FORCE_DELETE . " and " . FLAG_USE_EXISTING . " are mutually exclusive." . PHP_EOL;
    exit(1);
}

$only_count = 0;
if ($only_core) {
    $only_count++;
}
if ($only_library) {
    $only_count++;
}
if ($only_count > 1) {
    echo help_text();
    echo PHP_EOL;
    echo "Flags " . FLAG_ONLY_CORE . " and " . FLAG_ONLY_LIBRARY . " are mutually exclusive." . PHP_EOL;
    exit(1);
}

// determine what to generate
$generate_core = $only_core || (0 === $only_count);
$generate_library = $only_library || (0 === $only_count);

// determine if monolog is present, otherwise use null logger
if (class_exists('\\Monolog\\Logger')) {
    $logger = new \Monolog\Logger(
        'php-fhir',
        [
            (new \Monolog\Handler\StreamHandler('php://stdout', $log_level))
                ->setFormatter(
                    new \Monolog\Formatter\LineFormatter(\Monolog\Formatter\LineFormatter::SIMPLE_FORMAT),
                ),
        ],
        [
            new \Monolog\Processor\PsrLogMessageProcessor(\DateTimeInterface::W3C),
        ]
    );
} else {
    $logger = new NullLogger();
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
        echo 'Version "' . $vg . '" not found in config.  Available: ' .
            implode(', ', $config->listVersions()) . PHP_EOL . PHP_EOL;
        exit(1);
    }
}

$ins = [STDIN];
$null = null;

if (!$only_core) {
    echo PHP_EOL;

    echo 'Locating source(s) for versions: ' . implode(', ', $versions_to_generate) . PHP_EOL . PHP_EOL;

    foreach ($versions_to_generate as $version_name) {
        $version_name = trim($version_name);

        $version_config = $config->getVersion($version_name);

        $source_url = $version_config->getSourceUrl();
        $schema_dir = $version_config->getSchemaPath();

        // Download zip files
        $zip_file_name = $schema_dir . DIRECTORY_SEPARATOR . $version_name . '.zip';
        $zip_exists = file_exists($zip_file_name);

        $download = $unzip = true;

        if ($zip_exists) {
            if (!$use_existing && ($force_delete ||
                    ask("ZIP \"{$zip_file_name}\" already exists." . PHP_EOL . "Would you like to re-download from \"{$source_url}\"?"))
            ) {
                echo "Deleting {$zip_file_name} ..." . PHP_EOL;
                unlink($zip_file_name);
                if (file_exists($zip_file_name)) {
                    echo "Unable to delete file {$zip_file_name}" . PHP_EOL;
                    exit(1);
                }
                echo "File {$zip_file_name} deleted." . PHP_EOL;
            } else {
                echo "Using existing local copy" . PHP_EOL;
                $download = false;
            }
        }

        if ($download) {
            // Download zip file...
            echo "Downloading FHIR schema for version {$version_name} from {$source_url} to {$zip_file_name}" . PHP_EOL;
            $fh = fopen($zip_file_name, 'w');
            $ch = curl_init($source_url);
            curl_setopt_array(
                $ch,
                [
                    CURLOPT_USERAGENT => $user_agent,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HEADER => 0,
                    CURLOPT_FILE => $fh,
                ]
            );
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            fclose($fh);
            if ('' !== $err) {
                echo "Error downloading version {$version_name} from {$source_url}: {$err}" . PHP_EOL;
                exit(1);
            }
            if ($code !== 200) {
                echo "Error downloading version {$version_name} from {$source_url}: {$code} ({$resp})" . PHP_EOL;
                exit(1);
            }
        }

        if (is_dir($schema_dir)) {
            if (is_dir_empty($schema_dir)) {
                // TODO: is this necessary...?
                echo "Schema dir \"{$schema_dir}\" is empty, will remove and re-create" . PHP_EOL;
                nuke_dir($schema_dir);
                if (!mkdir($schema_dir, 0755, true)) {
                    echo "Unable to create directory \"{$schema_dir}\. Exiting" . PHP_EOL;
                    exit(1);
                }
            } elseif (!$download) {
                echo "Did not download new zip and schema dir \"{$schema_dir}\" already exists, using..." . PHP_EOL;
                $unzip = false;
            } elseif (!$use_existing) {
                if ($force_delete || ask("Schema dir \"{$schema_dir}\" already exists, ok to delete?")) {
                    nuke_dir($schema_dir);
                    if (!mkdir($schema_dir, 0755, true)) {
                        echo "Unable to create directory \"{$schema_dir}\. Exiting" . PHP_EOL;
                        exit(1);
                    }
                } else {
                    echo "Exiting" . PHP_EOL;
                    exit(0);
                }
            }
        }

        if ($unzip) {
            if (class_exists('\\ZipArchive')) {
                echo "ext-zip found" . PHP_EOL;

                $zip = new \ZipArchive();

                if (true !== ($res = $zip->open($schema_dir . '.zip'))) {
                    echo "Unable to open file {$schema_dir}.zip.  ZipArchive err: {$res}" . PHP_EOL;
                    exit(1);
                }

                // Extract Zip
                $zip->extractTo($schema_dir);
                $zip->close();
            } else {
                echo "ext-zip not found, trying \"unzip\" directly..." . PHP_EOL;
                $cmd = "unzip -o -qq {$schema_dir}.zip -d {$schema_dir}";
                $output = [];
                $code = 0;
                echo "executing: {$cmd}" . PHP_EOL;
                exec($cmd, $output, $code);
                if (0 !== $code) {
                    echo "unzip failed with code {$code}" . PHP_EOL . "output:" . PHP_EOL;
                    foreach ($output as $line) {
                        echo "-----> {$line}" . PHP_EOL;
                    }
                    exit(1);
                }
            }
        }
    }
}

echo PHP_EOL;

// create builder
$builder = new Builder($config);

// render entities based on flags.
$builder->render($generate_core, $generate_library ? $versions_to_generate : []);

echo PHP_EOL . 'Generation completed' . PHP_EOL;
