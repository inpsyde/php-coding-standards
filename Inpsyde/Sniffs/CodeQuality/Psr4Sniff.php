<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
final class Psr4Sniff implements Sniff
{
    /**
     * @var string|null
     */
    public $psr4 = null;

    /**
     * @var array
     */
    public $exclude = [];

    /**
     * @return int[]
     */
    public function register()
    {
        return [T_CLASS, T_INTERFACE, T_TRAIT];
    }

    /**
     * @param File $file
     * @param int $position
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    public function process(File $file, $position)
    {
        $className = $file->getDeclarationName($position);
        $code = $file->getTokens()[$position]['code'];
        $entityType = 'class';
        if ($code !== T_CLASS) {
            $entityType = $code === T_TRAIT ? 'trait' : 'interface';
        }

        $this->exclude = $this->normalizeExcluded($this->exclude);

        $validNamespace = is_array($this->psr4) && $this->psr4
            ? $this->checkNamespace($file, $position, $entityType, $className)
            : true;

        if (!$validNamespace) {
            return;
        }

        $fileName = basename($file->getFilename());
        if ($fileName === "{$className}.php") {
            return;
        }

        $file->addError(
            sprintf(
                "File containing %s '%s' is named '%s' instead of '%s'.",
                $entityType,
                $className,
                $fileName,
                "{$className}.php"
            ),
            $position,
            'WrongFilename'
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @param string $entityType
     * @param string $className
     * @return bool
     */
    private function checkNamespace(
        File $file,
        int $position,
        string $entityType,
        string $className
    ) {

        list($namespacePos, $namespace) = PhpcsHelpers::findNamespace($file, $position);

        $fullyQualifiedName = "{$namespace}\\{$className}";
        if (in_array($fullyQualifiedName, $this->exclude, true)) {
            return true;
        }

        list($baseNamespace, $baseFolder) = $this->classPsr4Info($namespace);

        if (!$baseNamespace || !$namespacePos) {
            $file->addError(
                sprintf(
                    "Namespace '%s' is not compliant with given PSR-4 configuration.",
                    $namespace
                ),
                $namespacePos,
                'NotInPSR4'
            );

            return false;
        }

        $namespaceRemain = trim(substr($namespace, strlen($baseNamespace)), '\\');
        $expectedDirChunks = explode('\\', $namespaceRemain);
        array_unshift($expectedDirChunks, $baseFolder);

        $classPath = dirname($file->getFilename());
        $classDirChunks = explode('/', str_replace('\\', '/', $classPath));
        $actualDirChunks = array_slice($classDirChunks, -1 * count($expectedDirChunks));

        if ($expectedDirChunks === $actualDirChunks) {
            return true;
        }

        $file->addError(
            sprintf(
                "%s '%s', located in folder '%s', is not compliant with PSR-4 configuration.",
                ucfirst($entityType),
                $fullyQualifiedName,
                $classPath
            ),
            $namespacePos,
            'InvalidPSR4'
        );

        return false;
    }

    /**
     * @param array $excluded
     * @return array
     */
    private function normalizeExcluded(array $excluded): array
    {
        return array_map(
            function (string $className): string {
                return ltrim($className, '\\');
            },
            $excluded
        );
    }

    /**
     * @param string $namespace
     * @return array
     */
    private function classPsr4Info(string $namespace): array
    {
        $classBaseNamespace = null;
        $classBaseFolder = null;
        foreach ($this->psr4 as $baseNamespace => $folder) {
            $baseNamespace = trim($baseNamespace, '\\');
            if (strpos($namespace, $baseNamespace) === 0) {
                $classBaseNamespace = $baseNamespace;
                $classBaseFolder = trim(str_replace('\\', '/', $folder), './');
                break;
            }
        }

        return [$classBaseNamespace, $classBaseFolder];
    }
}
