<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class Psr4Sniff implements Sniff
{
    /**
     * @var mixed
     */
    public $psr4;

    /**
     * @var mixed
     */
    public $exclude = [];

    /**
     * @return array<int>
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function register()
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        return [T_CLASS, T_INTERFACE, T_TRAIT];
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function process(File $file, $position)
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        $className = (string)$file->getDeclarationName($position);

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $code = $tokens[$position]['code'];
        $entityType = 'class';
        if ($code !== T_CLASS) {
            $entityType = $code === T_TRAIT ? 'trait' : 'interface';
        }

        $this->normalizeExcluded();

        if (!$this->psr4 || !is_array($this->psr4)) {
            $this->checkFilenameOnly($file, $position, $className, $entityType);

            return;
        }

        $this->checkPsr4($file, $position, $className, $entityType);
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
    ) {

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
     * @return void
     */
    private function checkPsr4(File $file, int $position, string $className, string $entityType)
    {
        list(, $namespace) = PhpcsHelpers::findNamespace($file, $position);
        $namespace = is_string($namespace) ? "{$namespace}\\" : '';
        $namespace = rtrim($namespace, '\\');

        $fullyQualifiedName = $namespace . "\\{$className}";

        foreach ($this->exclude as $excluded) {
            if (strpos($fullyQualifiedName, (string)$excluded) === 0) {
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
    private function normalizeExcluded()
    {
        $excluded = $this->exclude;
        if (!$excluded || !is_array($excluded)) {
            $this->exclude = [];

            return;
        }

        $this->exclude = [];
        foreach ($excluded as $className) {
            is_string($className) and $this->exclude[] = ltrim($className, '\\');
        }
    }
}
