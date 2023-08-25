<?php

/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class ArgumentTypeDeclarationSniff implements Sniff
{
    public const TYPE_CODES = [
        T_STRING,
        T_ARRAY_HINT,
        T_CALLABLE,
        T_SELF,
    ];

    public const METHODS_WHITELIST = [
        'unserialize',
        'seek',
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_FUNCTION, T_CLOSURE];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Generic.Metrics.CyclomaticComplexity

        if ($this->shouldIgnore($phpcsFile, $stackPtr)) {
            return;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();
        $paramsStart = (int)($tokens[$stackPtr]['parenthesis_opener'] ?? 0);
        $paramsEnd = (int)($tokens[$stackPtr]['parenthesis_closer'] ?? 0);

        if (!$paramsStart || !$paramsEnd || $paramsStart >= ($paramsEnd - 1)) {
            return;
        }

        $docBlockTypes = PhpcsHelpers::functionDocBlockParamTypes($phpcsFile, $stackPtr);
        $variables = PhpcsHelpers::filterTokensByType($paramsStart, $paramsEnd, $phpcsFile, T_VARIABLE);

        foreach ($variables as $varPosition => $varToken) {
            // Not triggering error for variable explicitly declared as mixed (or mixed|null)
            if ($this->isMixed((string)($varToken['content'] ?? ''), $docBlockTypes)) {
                continue;
            }

            $typePosition = $phpcsFile->findPrevious(
                [T_WHITESPACE, T_ELLIPSIS, T_BITWISE_AND],
                $varPosition - 1,
                $paramsStart + 1,
                true
            );

            $type = $tokens[$typePosition] ?? null;
            if ($type && !in_array($type['code'] ?? '', self::TYPE_CODES, true)) {
                $phpcsFile->addWarning('Argument type is missing', $stackPtr, 'NoArgumentType');
            }
        }
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return bool
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    private function shouldIgnore(File $phpcsFile, $stackPtr): bool
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $name = $phpcsFile->getDeclarationName($stackPtr);

        return PhpcsHelpers::functionIsArrayAccess($phpcsFile, $stackPtr)
            || PhpcsHelpers::isHookClosure($phpcsFile, $stackPtr)
            || PhpcsHelpers::isHookFunction($phpcsFile, $stackPtr)
            || PhpcsHelpers::isUntypedPsrMethod($phpcsFile, $stackPtr)
            || (
                PhpcsHelpers::functionIsMethod($phpcsFile, $stackPtr)
                && in_array($name, self::METHODS_WHITELIST, true)
            );
    }

    /**
     * @param string $paramName
     * @param array<string, array<string>> $docBlockTypes
     * @return bool
     */
    private function isMixed(string $paramName, array $docBlockTypes): bool
    {
        $paramDocBlockTypes = $paramName ? ($docBlockTypes[$paramName] ?? null) : null;
        if (!$paramDocBlockTypes) {
            return false;
        }

        $paramDocBlockTypesCount = count($paramDocBlockTypes);
        if ($paramDocBlockTypesCount !== 1 && $paramDocBlockTypesCount !== 2) {
            return false;
        }

        $paramDocBlockTypes = array_map('trim', $paramDocBlockTypes);
        if (!in_array('mixed', $paramDocBlockTypes, true)) {
            return false;
        }

        return ($paramDocBlockTypesCount === 1) || in_array('null', $paramDocBlockTypes, true);
    }
}
