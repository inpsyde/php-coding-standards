<?php

/*
 * This file is part of the "php-coding-standards" package.
 *
 * Copyright (c) 2023 Inpsyde GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;

class Psr4Sniff implements Sniff
{
    public array $psr4 = [];
    public array $exclude = [];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $className = ObjectDeclarations::getName($phpcsFile, $stackPtr) ?? '';

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();
        $code = $tokens[$stackPtr]['code'];
        $entityType = 'class';
        if ($code !== T_CLASS) {
            $entityType = $code === T_TRAIT ? 'trait' : 'interface';
        }

        $this->normalizeExcluded();

        if (!$this->psr4) {
            $this->checkFilenameOnly($phpcsFile, $stackPtr, $className, $entityType);

            return;
        }

        $this->checkPsr4($phpcsFile, $stackPtr, $className, $entityType);
    }

    /**
     * @param File $file
     * @param int $position
     * @param string $className
     * @param string $entityType
     * @return void
     */
    private function checkFilenameOnly(
        File $file,
        int $position,
        string $className,
        string $entityType
    ): void {

        if (basename($file->getFilename()) === "{$className}.php") {
            return;
        }

        $file->addError(
            sprintf(
                "File containing %s '%s' is named '%s' instead of '%s'.",
                $entityType,
                $className,
                $file->getFilename(),
                "{$className}.php"
            ),
            $position,
            'WrongFilename'
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @param string $className
     * @param string $entityType
     * @return void
     */
    private function checkPsr4(
        File $file,
        int $position,
        string $className,
        string $entityType
    ): void {

        $namespace = Namespaces::determineNamespace($file, $position);

        $fullyQualifiedName = $namespace . "\\{$className}";

        foreach ($this->exclude as $excluded) {
            if (strpos($fullyQualifiedName, (string) $excluded) === 0) {
                return;
            }
        }

        $filePath = str_replace('\\', '/', $file->getFilename());

        foreach ($this->psr4 as $baseNamespace => $foldersStr) {
            if (!is_string($baseNamespace) || !is_string($foldersStr)) {
                continue;
            }

            $baseNamespace = trim($baseNamespace, '\\');
            if (strpos($namespace, $baseNamespace) !== 0) {
                continue;
            }

            $folders = explode('|', $foldersStr);

            $valid = $this->checkPsr4Folders(
                $filePath,
                $baseNamespace,
                $namespace,
                $className,
                ...$folders
            );

            if ($valid) {
                return;
            }
        }

        $file->addError(
            sprintf(
                "%s '%s', located at '%s', is not compliant with PSR-4 configuration.",
                ucfirst($entityType),
                $fullyQualifiedName,
                $filePath
            ),
            $position,
            'InvalidPSR4'
        );
    }

    /**
     * @param string $filePath
     * @param string $baseNamespace
     * @param string $namespace
     * @param string $className
     * @param string ...$folders
     * @return bool
     */
    private function checkPsr4Folders(
        string $filePath,
        string $baseNamespace,
        string $namespace,
        string $className,
        string ...$folders
    ): bool {

        foreach ($folders as $folder) {
            $folder = trim(str_replace('\\', '/', $folder), './');
            $folderSplit = explode("/{$folder}/", $filePath);
            if (count($folderSplit) < 2) {
                continue;
            }

            $relativePath = array_pop($folderSplit);

            if (basename($relativePath) !== "{$className}.php") {
                continue;
            }

            $relativeNamespace = str_replace('/', '\\', dirname($relativePath));
            $expectedNamespace = $relativeNamespace === '.'
                ? $baseNamespace
                : "{$baseNamespace}\\{$relativeNamespace}";

            if ("{$expectedNamespace}\\{$className}" === "{$namespace}\\{$className}") {
                return true;
            }
        }

        return false;
    }

    /**
     * @return void
     * @psalm-assert array<string> $this->exclude
     */
    private function normalizeExcluded(): void
    {
        $excluded = $this->exclude;

        $this->exclude = [];
        foreach ($excluded as $className) {
            is_string($className) and $this->exclude[] = ltrim($className, '\\');
        }
    }
}
