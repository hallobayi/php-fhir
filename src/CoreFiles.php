<?php declare(strict_types=1);

namespace DCarbone\PHPFHIR;

/*
 * Copyright 2024-2025 Daniel Carbone (daniel.p.carbone@gmail.com)
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

class CoreFiles
{
    /** @var string */
    private string $_outputDir;
    /** @var string */
    private string $_templateDir;

    /** @var \DCarbone\PHPFHIR\CoreFile[] */
    private array $_files;

    /**
     * @param \DCarbone\PHPFHIR\Config $config
     * @param string $outputDir
     * @param string $templateDir
     * @param string $baseNS
     */
    public function __construct(Config $config, string $outputDir, string $templateDir, string $baseNS)
    {
        $this->_outputDir = realpath($outputDir);
        $this->_templateDir = realpath($templateDir);
        $rootLen = strlen($this->_templateDir);

        foreach ($this->getTemplateFileIterator() as $fpath => $fi) {
            /** @var $fi \SplFileInfo */

            $outDir = $this->_outputDir;
            $outNS = $baseNS;

            $sub = substr($fi->getPath(), $rootLen);
            if ('' !== $sub) {
                $outNS .= PHPFHIR_NAMESPACE_SEPARATOR . NameUtils::templateFilenameToPHPName($sub, DIRECTORY_SEPARATOR, PHPFHIR_NAMESPACE_SEPARATOR);
            }

            $outDir .= DIRECTORY_SEPARATOR . NameUtils::templateFilenameToPHPName($outNS, PHPFHIR_NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR);

            $this->_files[] = new CoreFile($config, $fpath, $outDir, $outNS);
        }
    }

    /**
     * @return string
     */
    public function getOutputDir(): string
    {
        return $this->_outputDir;
    }

    /**
     * @return string
     */
    public function getTemplateDir(): string
    {
        return $this->_templateDir;
    }

    public function getEntityNames(): array
    {
        $out = [];
        foreach($this->_files as $file) {
            $out[] = $file->getEntityName();
        }
        natcasesort($out);
        return $out;
    }

    public function getCoreFileByEntityName(string $name): CoreFile
    {
        foreach ($this->_files as $file) {
            if ($file->getEntityName() === $name) {
                return $file;
            }
        }
        throw new \OutOfBoundsException(sprintf(
            'Unable to locate CoreFile for entity name "%s", available: [%s]',
            $name,
            implode(', ', $this->getEntityNames()),
        ));
    }

    /**
     * @return \RecursiveIteratorIterator
     */
    public function getTemplateFileIterator(): \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->_templateDir,
                \FilesystemIterator::CURRENT_AS_FILEINFO |
                \FilesystemIterator::SKIP_DOTS,
            ),
        );
    }

    /**
     * @return \DCarbone\PHPFHIR\CoreFile[]
     */
    public function getIterator(): iterable
    {
        return new \ArrayIterator($this->_files);
    }
}