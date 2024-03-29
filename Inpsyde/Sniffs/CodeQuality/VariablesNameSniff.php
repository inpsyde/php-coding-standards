<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\Scopes;

class VariablesNameSniff implements Sniff
{
    public const GLOBALS = [
        '$_GET',
        '$_POST',
        '$_REQUEST',
        '$_SERVER',
        '$_COOKIE',
        '$_FILES',
        '$_SESSION',
        '$_ENV',
        '$GLOBALS',
    ];

    public const WP_GLOBALS = [
        '$charset_collate',
        '$current_user',
        '$interim_login',
        '$is_apache',
        '$is_chrome',
        '$is_edge',
        '$is_gecko',
        '$is_IE',
        '$is_IIS',
        '$is_iis7',
        '$is_iphone',
        '$is_lynx',
        '$is_macIE',
        '$is_NS4',
        '$is_opera',
        '$is_safari',
        '$is_winIE',
        '$manifest_version',
        '$required_mysql_version',
        '$required_php_version',
        '$super_admins',
        '$tinymce_version',
    ];

    public string $checkType = 'camelCase';
    public array $ignoredNames = [];
    public bool $ignoreLocalVars = false;
    public bool $ignoreProperties = false;

    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_VARIABLE];
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

        $ignored = $this->allIgnored();

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();
        $name = (string) $tokens[$stackPtr]['content'];

        if (
            in_array($name, $ignored, true)
            || strpos($name, '$wp_') === 0
            || strpos($name, '$_wp_') === 0
        ) {
            return;
        }

        $isCamelCase = $this->checkType() === 'camelCase';

        $valid = $isCamelCase ? $this->checkCamelCase($name) : $this->checkSnakeCase($name);
        if ($valid) {
            return;
        }

        $isProperty = Scopes::isOOProperty($phpcsFile, $stackPtr);

        if (
            ($isProperty && $this->arePropertiesIgnored())
            || (!$isProperty && $this->areVariablesIgnored())
        ) {
            return;
        }

        $phpcsFile->addWarning(
            sprintf(
                '"%s" should be used for variable names.',
                $isCamelCase ? '$camelCase' : '$snake_case'
            ),
            $stackPtr,
            $isCamelCase ? 'SnakeCaseVar' : 'CamelCaseVar'
        );
    }

    /**
     * @return string
     */
    private function checkType(): string
    {
        $type = strtolower(trim($this->checkType));
        if (in_array($type, ['camelcase', 'snake_case'], true)) {
            return $type === 'camelcase' ? 'camelCase' : 'snake_case';
        }

        return 'camelCase';
    }

    /**
     * @return bool
     */
    private function arePropertiesIgnored(): bool
    {
        return filter_var($this->ignoreProperties, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return bool
     */
    private function areVariablesIgnored(): bool
    {
        return filter_var($this->ignoreLocalVars, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkCamelCase(string $name): bool
    {
        return preg_match('~^\$[a-z]+(?:[a-zA-Z0-9]+)?$~', $name)
            && !preg_match('~[A-Z]{2,}~', $name);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkSnakeCase(string $name): bool
    {
        return (bool) preg_match('~^\$[a-z]+(?:[a-z0-9_]+)?$~', $name);
    }

    /**
     * @return array
     *
     * psalm-assert array<string> $this->ignoredNames
     */
    private function allIgnored(): array
    {
        $ignored = $this->ignoredNames;

        /** @var list<string> $normalized */
        $normalized = [];
        foreach ($ignored as $name) {
            if (is_string($name)) {
                $normalized[] = '$' . ltrim(trim($name), '$');
            }
        }

        $this->ignoredNames = $normalized;

        return array_merge($normalized, self::GLOBALS, self::WP_GLOBALS);
    }
}
