<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\CodingStandard\Helpers\FunctionDocBlock;
use Inpsyde\CodingStandard\Helpers\Functions;
use Inpsyde\CodingStandard\Helpers\WpHooks;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;

class ArgumentTypeDeclarationSniff implements Sniff
{
    public const METHODS_WHITELIST = [
        'unserialize',
        'seek',
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_FUNCTION, T_CLOSURE, T_FN];
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

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        if ($this->shouldIgnore($phpcsFile, $stackPtr, $tokens)) {
            return;
        }

        /** @var array<array{name: string, type_hint?: string|false}> $parameters */
        $parameters = FunctionDeclarations::getParameters($phpcsFile, $stackPtr);
        $docBlockTypes = FunctionDocBlock::allParamTypes($phpcsFile, $stackPtr);

        $errors = [];
        foreach ($parameters as $parameter) {
            $typeHint = $parameter['type_hint'] ?? '';
            if (($typeHint !== '') && ($typeHint !== false)) {
                continue;
            }

            $docTypes = $docBlockTypes[$parameter['name']] ?? [];
            if (!Functions::isNonDeclarableDocBlockType($docTypes, false)) {
                $errors[] = $parameter['name'];
            }
        }

        if (!$errors) {
            return;
        }

        $allErrors = implode('", "', $errors);
        $phpcsFile->addWarning(
            sprintf('Argument type is missing for parameter(s) "%s"', $allErrors),
            $stackPtr,
            'NoArgumentType'
        );
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array<int, array<string, mixed>> $tokens
     * @return bool
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    private function shouldIgnore(File $phpcsFile, $stackPtr, array $tokens): bool
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $tokenCode = $tokens[$stackPtr]['code'] ?? '';
        $name = ($tokenCode !== T_FN) ? FunctionDeclarations::getName($phpcsFile, $stackPtr) : '';

        return Functions::isArrayAccess($phpcsFile, $stackPtr)
            || WpHooks::isHookClosure($phpcsFile, $stackPtr)
            || WpHooks::isHookFunction($phpcsFile, $stackPtr)
            || Functions::isPsrMethod($phpcsFile, $stackPtr)
            || FunctionDeclarations::isSpecialMethod($phpcsFile, $stackPtr)
            || (
                Scopes::isOOMethod($phpcsFile, $stackPtr)
                && in_array($name, self::METHODS_WHITELIST, true)
            );
    }
}
